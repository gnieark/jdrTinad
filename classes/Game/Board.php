<?php

class Board {
    private string $game_name;
    private array $allowedCreatures = array();
    private string $urlpart;
    private array $playTurns = array();
    private string $gameSummary = "Début de partie.";
    private string $saveUid;
    private int $step;

    public function __construct() { $this->step = 0; }

    // === Getters / Setters ===
    public function get_gameSummary(): string { return $this->gameSummary; }
    public function set_gameSummary(string $summary): self { $this->gameSummary = $summary; return $this; }

    public function get_saveUid(): string { return $this->saveUid; }

    public function get_playTurns(): array { return $this->playTurns; }
    public function get_lastPlayTurn(): PlayTurn { return end($this->playTurns); }

    public function get_game_name(): string { return $this->game_name; }
    public function set_game_name(string $name): Board {
        $this->game_name = $name;
        $this->testStep0To1();
        return $this;
    }

    public function get_allowedCreatures(): array { return $this->allowedCreatures; }
    public function add_allowedCreature(string $name): Board {
        $this->allowedCreatures[] = $name;
        $this->testStep0To1();
        return $this;
    }
    public function set_allowedCreatures(array $creatures): Board {
        $this->allowedCreatures = $creatures;
        $this->testStep0To1();
        return $this;
    }

    public function get_urlpart(): string { return $this->urlpart; }
    public function set_urlpart(string $part = ""): Board {
        $newurlpart = empty($part) ? uniqid() : $part;

        if (isset($this->urlpart)) {
            rename("../gamesdatas/" . $this->urlpart, "../gamesdatas/" . $newurlpart);
        } else {
            mkdir("../gamesdatas/" . $newurlpart, 0700);
        }

        $this->urlpart = $newurlpart;
        $this->testStep0To1();
        return $this;
    }

    public function get_save_real_path(): string {
        return realpath("../gamesdatas/" . $this->urlpart);
    }

    // === Ajout d'objets ===
    public function add_playTurn(PlayTurn $playTurn): Board {
        $this->playTurns[] = $playTurn;
        return $this;
    }

    public function add_playerResponse(PlayerResponse $playerResponse): bool {
        $lastTurn = end($this->playTurns);
        if ($playerResponse->get_playTurnUID() !== $lastTurn->get_turnUID()) return false;

        $folderPath = "../gamesdatas/" . $this->urlpart . "/turn-" . $playerResponse->get_playTurnUID();
        if (!is_dir($folderPath)) mkdir($folderPath, 0700, true);

        $savePath = $folderPath . "/" . $playerResponse->get_playerUID() . ".txt";
        $playerResponse->save($savePath);
        return true;
    }

    // === Méthodes métiers ===
    public function regen_gameSummary(): self {
        $tplPrompt = new TplBlock();

        $players = $this->get_players();
        $playersArr = array_map(fn($p) => $p->__toArray(), $players);
        $tplPrompt->addVars(["players" => json_encode($playersArr, true)]);

        $turnsArr = [];
        foreach ($this->get_playTurns() as $turn) {
            $turn->loadPlayersResponses($this->get_urlpart());
            $turnsArr[] = $turn->__toArrayToPlay();
        }
        $tplPrompt->addVars(["historyJSON" => json_encode($turnsArr, true)]);

        $promptToSend = $tplPrompt->applyTplFile("../templates/prompts/promptIA-RegenSummary.txt");
        $rep = PlayTurn::sendMessageToIa($promptToSend, $this);
        $this->set_gameSummary($rep["storyState"]);
        return $this->save();
    }

    public function closeLastTurn(): self {
        if (!empty($this->playTurns)) {
            $this->playTurns[count($this->playTurns) - 1]->close();
            $this->save();
        }
        return $this;
    }

    public function newGameTurn() {}

    public function get_player_by_uid(string $uid): Player {
        $filePath = "../gamesdatas/" . $this->urlpart . "/player-" . $uid;
        if (!file_exists($filePath)) throw new Exception("no player found with uid ." . $uid);
        return Player::loadPlayer($filePath);
    }

    public function get_players(): array {
        $players = [];
        $folderPath = "../gamesdatas/" . $this->urlpart . "/";
        if (!is_dir($folderPath)) return $players;

        foreach (scandir($folderPath) as $file) {
            if (str_starts_with($file, "player-")) {
                $filePath = $folderPath . $file;
                $players[] = Player::loadPlayer($filePath);
            }
        }
        return $players;
    }

    public function get_PlayTurnByUid(string $turnUid): PlayTurn {
        foreach ($this->playTurns as $playTurn) {
            if ($playTurn->get_turnUID() == $turnUid) {
                $playTurn->loadPlayersResponses($this->urlpart);
                return $playTurn;
            }
        }
        throw new Exception("no turn found with uid ." . $turnUid);
    }

    public function save(): Board {
        if (empty($this->urlpart)) throw new Exception("Cannot save: urlpart is not set.");
        $folderPath = "../gamesdatas/" . $this->urlpart;
        if (!is_dir($folderPath)) mkdir($folderPath, 0700, true);
        $this->saveUid = uniqid();
        file_put_contents($folderPath . "/board.txt", serialize($this));
        return $this;
    }

    public function delete_board(): void {
        $folderPath = "../gamesdatas/" . $this->urlpart;
        self::deleteDirectory($folderPath);

        $sql = "DELETE FROM `" . UserGroupManager::get_users_boards_rel_table() . "` WHERE board_uid=:boarduid;";
        $db = Database::get_db();
        $sth = $db->prepare($sql);
        $boarduid = $this->get_urlpart();
        $sth->bindParam(':boarduid', $boarduid, PDO::PARAM_STR);
        $sth->execute();
    }

    // === Méthodes internes ===
    private function testStep0To1(): void {
        if ($this->step == 0 && isset($this->game_name, $this->urlpart) && !empty($this->allowedCreatures)) {
            $this->step = 1;
        }
    }

    private static function deleteDirectory($dir) {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                if (!self::deleteDirectory($path)) return false;
            } else {
                if (!unlink($path)) return false;
            }
        }
        return rmdir($dir);
    }

    // === Méthodes statiques ===
    public static function boardFileExists(string $urlPart): bool {
        return file_exists("../gamesdatas/" . $urlPart . "/board.txt");
    }

    public static function loadBoard(string $urlPart): Board {
        $path = "../gamesdatas/" . $urlPart . "/board.txt";
        if (!file_exists($path)) throw new Exception("Cannot load: file not found at $path.");
        return unserialize(file_get_contents($path));
    }
}