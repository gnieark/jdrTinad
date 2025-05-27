<?php
class PlayerResponse {
    private string $playerUID;
    private string $playTurnUID;
    private string $playerresponse;
    private bool $needDiceRoll;
    private array $testedSkills = array();
    private array $diceBonus = array(); // should be negative for a bonus
    private bool $diceRollSuccess;
    private bool $diceResultCritical;
    private array $diceScores = array();
    private array $playerResponsesCategories = array();
    private string $responseanalysis = "";

    public function __construct(string $playTurnUID, string $playerUID) {
        $this->set_playerUID($playerUID)
             ->set_playTurnUID($playTurnUID);
    }

    // === Getters / Setters ===
    public function get_playerUID(): string { return $this->playerUID; }
    public function set_playerUID(string $uid): PlayerResponse {
        $this->playerUID = $uid;
        return $this;
    }

    public function get_playTurnUID(): string { return $this->playTurnUID; }
    public function set_playTurnUID(string $playTurnUID): PlayerResponse {
        $this->playTurnUID = $playTurnUID;
        return $this;
    }

    public function get_playerresponse(): string { return $this->playerresponse; }
    public function set_playerresponse(string $playerresponse): PlayerResponse {
        $this->playerresponse = $playerresponse;
        return $this;
    }

    // === Méthodes métiers ===
    public function _toArrayToPlay() {
        $arr = [
            "playerUID" => $this->playerUID,
            "playTurnUID" => $this->playTurnUID,
            "player_response" => $this->playerresponse,
            "tested_skills" => $this->testedSkills,
            "responseanalysis" => $this->responseanalysis
        ];
        if (!empty($this->testedSkills)) {
            $arr["dices_bonus"] = $this->diceBonus;
            $arr["dices_scores"] = $this->diceScores;
            $arr["dices_succes"] = $this->diceRollSuccess;
            $arr["dices_critical"] = $this->diceResultCritical;
        }
        return $arr;
    }

    public function analyseResponse(Board $board) {
        if (!isset($this->playerresponse) || trim($this->playerresponse) === '') {
            throw new \LogicException("La réponse du joueur n'a pas été définie avant l'analyse.");
        }

        $tpl = new TplBlock();
        $playersArr = array_map(fn($player) => $player->__toArray(), $board->get_players());
        $lastTurn = $board->get_lastPlayTurn();

        $tpl->addVars([
            'players' => json_encode($playersArr, true),
            'summary' => $board->get_gameSummary(),
            'lastturn' => json_encode($lastTurn->__toArrayToPlay(null, false), true),
            'playerResponse' => $this->get_playerresponse(),
            'playeruid' => $this->get_playerUID()
        ]);

        $promptIa = $tpl->applyTplFile("../templates/prompts/promptIA-Analyseawnser.txt");
        $repIA = PlayTurn::sendMessageToIa($promptIa, $board);
        $player = $board->get_player_by_uid($this->get_playerUID());

        if (!empty($repIA["competences_a_tester"])) {
            $this->diceRollSuccess = true;
            $this->diceResultCritical = false;

            foreach ($repIA["competences_a_tester"] as $competence) {
                $diceScore = random_int(0, 20);
                $this->diceScores[] = $diceScore;
                $this->testedSkills[] = $competence;

                $competanceValue = match($competence) {
                    "courage" => $player->getCourage(),
                    "intelligence" => $player->getIntelligence(),
                    "charisme" => $player->getCharisma(),
                    "dexterite" => $player->getDexterity(),
                    "force" => $player->getStrength(),
                    default => 10
                };
                if (!in_array($competence, ["courage", "intelligence", "charisme", "dexterite", "force"])) {
                    error_log("L'IA demande de tester une compétence inconnue: $competence");
                }

                $bonus = ($repIA["bonus"] > 0)
                    ? intval(((19 - $competanceValue) * $repIA["bonus"]) / 10)
                    : intval((($competanceValue + 1) * $repIA["bonus"]) / 10);

                $this->diceBonus[] = $bonus;

                if ($diceScore > $competanceValue + $bonus) $this->diceRollSuccess = false;
                if (in_array($diceScore, [0, 20])) $this->diceResultCritical = true;
            }

            foreach ($repIA["categories"] as $cat) {
                $this->playerResponsesCategories[] = $cat;
            }

            if (!$repIA["reponse_coherente"]) {
                $this->responseanalysis = file_get_contents("../templates/prompts/promptIA-Unconsistent.txt");
            } elseif (isset($this->diceRollSuccess)) {
                $this->responseanalysis = match(true) {
                    $this->diceRollSuccess && $this->diceResultCritical => file_get_contents("../templates/prompts/promptIA-Success-critical.txt"),
                    $this->diceRollSuccess => file_get_contents("../templates/prompts/promptIA-Success-normal.txt"),
                    !$this->diceRollSuccess && $this->diceResultCritical => file_get_contents("../templates/prompts/promptIA-Fail-critical.txt"),
                    default => file_get_contents("../templates/prompts/promptIA-Fail-normal.txt")
                };
            } else {
                $this->responseanalysis = "L'animateur du jeu autorise le joueur à faire cette action sans jet de dés. Elle est considérée comme réussie.";
            }

            if ($player->getPv() == 0) {
                $this->responseanalysis .= file_get_contents("../templates/prompts/promptIA-Playeurunconscious.txt");
            } elseif ($player->getPv() < 0 && $player->getPv() > -10) {
                $this->responseanalysis .= file_get_contents("../templates/prompts/promptIA-Playerdying.txt");
            } elseif ($player->getPv() < -9) {
                $this->responseanalysis .= file_get_contents("../templates/prompts/promptIA-PlayerDied.txt");
            }
        }
    }

    // === Sauvegarde ===
    public function save(string $file): self {
        file_put_contents($file, serialize($this));
        return $this;
    }

    // === Méthodes statiques ===
    public static function load($file): self {
        $data = file_get_contents($file);
        return unserialize($data);
    }
}