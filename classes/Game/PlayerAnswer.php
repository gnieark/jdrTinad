<?php
class PlayerAnswer {
    private string $playerUID;
    private string $playTurnUID;
    private string $playeranswer;
    private bool $needDiceRoll;
    private string $skillToTest;
    private bool $diceRollSuccess;
    private bool $diceResultCritical;


    public function __Construct(string $playTurnUID, string $playerUID){
        $this->set_playerUID($playerUID)
              ->set_playerUID($playTurnUID);
    }
    public function set_playerUID( string $uid ) :PlayerAnswer{
        $this->playerUID = $uid;
        return $this;
    }
    public function get_playerUID():string{
        return $this->playerUID;
    }
    public function set_playTurnUID(string $playTurnUID ):PlayerAnswer{
        $this->playTurnUID = $playTurnUID;
        return $this;
    }
    public function get_playTurnUID(): string{
        return $this->playTurnUID;
    }
    public function set_playeranswer(string $playeranswer):PlayerAnswer{
        $this->playeranswer = $playeranswer;
        return $this;
    }
    public function get_playeranswer():string{
        return $this->playeranswer;
    }
    
}