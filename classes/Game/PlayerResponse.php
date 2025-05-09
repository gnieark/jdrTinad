<?php
class  PlayerResponse{
    private string $playerUID;
    private string $playTurnUID;
    private string $playerresponse;
    private bool $needDiceRoll;
    private array $testedSkills = array();
    private array $diceBonus = array(); //should be nagative for a bonus 
    private bool $diceRollSuccess;
    private bool $diceResultCritical;
    private array $diceScores = array();
    private array $playerResponsesCategories = array();
    private string $responseanalysis = "";

    public function _toArrayToPlay(){
        $arr = array(
            "playerUID"         => $this->playerUID,
            "playTurnUID"       => $this->playTurnUID,
            "player_response"   => $this->playerresponse,
            "tested_skills"     => $this->testedSkills,
            "responseanalysis"  => $this->responseanalysis
        );
        if(!empty($this->testedSkills)){
            $arr["dices_bonus"]     = $this->diceBonus;
            $arr["dices_scores"]    = $this->diceScores;
            $arr["dices_succes"]    = $this->diceRollSuccess;
            $arr["dices_critical"]  = $this->diceResultCritical;

        }
        return $arr;
    }
    public function __Construct(string $playTurnUID, string $playerUID){
        $this->set_playerUID($playerUID)
              ->set_playTurnUID($playTurnUID);
    }
    public function set_playerUID( string $uid ) :PlayerResponse{
        $this->playerUID = $uid;
        return $this;
    }
    public function get_playerUID():string{
        return $this->playerUID;
    }
    public function set_playTurnUID(string $playTurnUID ):PlayerResponse{
        $this->playTurnUID = $playTurnUID;
        return $this;
    }
    public function get_playTurnUID(): string{
        return $this->playTurnUID;
    }
    public function set_playerresponse(string $playerresponse):PlayerResponse{
        $this->playerresponse = $playerresponse;
        return $this;
    }
    public function get_playerresponse():string{
        return $this->playerresponse;
    }
    /*
    * Parent Board is needed to have more context
    */
    public function analyseResponse(Board $board){
        if (!isset($this->playerresponse) || trim($this->playerresponse) === '') {
            throw new \LogicException("La réponse du joueur n'a pas été définie avant l'analyse.");
        }

        $tpl = new TplBlock();


        $playersArr = array();
        foreach($board->get_players() as $player){
            $playersArr[] = $player->__toArray();
        }

        $historyArr = array();
        foreach( $board->get_playTurns() as $playTurn) {
            $personalizedMessages = array();
           
            foreach($playTurn->get_personalisedAwnsers() as $playerUid => $message ){
                $personalizedMessages[] = array(
                    "player_uid"    => $playerUid,
                    "message"       => $message
                );
            }
            $playerResponsesArr = array();  //to do
           

            $historyArr[] = array(
                "MJ-GlobalMessage"  => $playTurn->get_allAwnser(),
                "MJ-PersonnalizedMessages"  => $personalizedMessages ,
                "PlayersResponses"    => array() //to do
            );
        }

        $tpl->addVars(
            array(
                'players'       => json_encode( $playersArr, true ),
                'historyJson'   => json_encode( $historyArr, true ),
                'playerResponse' => $this->get_playerresponse(),
                'playeruid'     => $this->get_playerUID()

            )

        );


        $promptIa = $tpl->applyTplFile("../templates/prompts/promptIA-Analyseawnser.txt");
        $repIA = PlayTurn::sendMessageToIa( $promptIa );

        $player = $board->get_player_by_uid( $this->get_playerUID() );

        //jet de dé

        if( !empty( $repIA["competances_a_tester"] ) ){
            //have to test dices
            //$this->diceBonus = $repIA["bonus"];
            $this->diceRollSuccess = true;
            $this->diceResultCritical = false;
            foreach( $repIA["competances_a_tester"] as $competence){
                //jet de dé
                $diceScore = random_int(0, 20);
                $this->diceScores[] = $diceScore;
                $this->testedSkills[] = $competence;
                switch($competence){
                    case "courage":
                        $competanceValue = $player->getCourage();
                        break;
                    case "intelligence":
                        $competanceValue = $player->getIntelligence();
                        break;
                    case "charisme":
                        $competanceValue = $player->getCharisma();
                        break;
                    case "dexterite":
                        $competanceValue = $player->getDexterity();
                        break;
                    case "force":
                        $competanceValue = $player->getStrength();
                        break;

                    default:
                        $competanceValue = 10; 
                        //should never happen
                        error_log("L'IA demande de tester une compétance inconnue" . $competence );
                        break;

                }
                //$this->$diceBonus
                if($repIA["bonus"] > 0){
                    $bonus = intval((( 19 - $competanceValue ) * $repIA["bonus"] ) / 10);
                    //bonus positif
                }else{
                    //bonus negatif
                    $bonus = intval((( $competanceValue +1 ) * $repIA["bonus"] ) / 10);
                }
                $this->diceBonus[] = $bonus;

                if( $diceScore > $competanceValue + $bonus ){
                    $this->diceRollSuccess = false;
                }
                if ($diceScore  == 0){
                    $this->diceResultCritical = true;
                }
                if ($diceScore  == 20){
                    $this->diceResultCritical= true;
                }
            }

            //categories
            foreach( $repIA["categories"] as $cat ){
                $playerResponsesCategories[] = $cat;
            }
           
            //analyse:

            if( !$repIA["reponse_coherente"] ){
                $this->responseanalysis = file_get_contents("../templates/prompts/promptIA-Unconsistent.txt");
            }elseif( ( isset($this->diceRollSuccess) ) ){
                if( $this->diceRollSuccess ){
                    //succes
                    if($this->diceResultCritical){
                        $this->responseanalysis = file_get_contents("../templates/prompts/promptIA-Success-critical.txt");
                    }else{
                        $this->responseanalysis = file_get_contents("../templates/prompts/promptIA-Success-normal.txt");
                    }

                }else{
                    //echec
                    if($this->diceResultCritical){
                        $this->responseanalysis = file_get_contents("../templates/prompts/promptIA-Fail-critical.txt");
                    }else{
                        $this->responseanalysis = file_get_contents("../templates/prompts/promptIA-Fail-normal.txt");
                    }

                }

            }else{
                //réponse cohérente qui ne nécessite pas de jet de dé.
                $this->responseanalysis = "L'animateur du jeu autorise le joueur à faire cette action sans jet de dés. Elle est considérée comme réussie.";

            }


        }
    }

    public function save( string $file) :self{
        file_put_contents( $file, serialize($this) );
        return $this;
    }

    public static function load($file):self{
            $data = file_get_contents($file);
            return unserialize($data); 
    }

}