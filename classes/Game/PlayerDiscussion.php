<?php
class PlayerDiscussion {
    private string $playerUID;
    private string $mjprompt;
    private string $playeranswer;
    private bool $needDiceRoll;
    private string $skillToTest;
    private bool $diceRollSuccess;
    private bool $diceResultCritical;


    public function set_playerUID( string $uid ) : PlayerDiscussion{
        $this->playerUID = $uid;
        return $this;
    }
    public function get_playerUID():string{
        return $this->playerUID;
    }
    public function set_mjprompt(string $mjprompt ): PlayerDiscussion{
        $this->mjprompt = $mjprompt;
        return $this;
    }
    public function get_mjprompt(): string{
        return $this->mjprompt;
    }
    public function set_playeranswer(string $playeranswer): PlayerDiscussion{
        $this->playeranswer = $playeranswer;
        return $this;
    }
    public function get_playeranswer():string{
        return $this->playeranswer;
    }
    
}