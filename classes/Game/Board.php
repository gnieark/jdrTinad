<?php

class Board{
    private string $game_name;
    private array $allowedCreatures = array();
    private string $urlpart;
    private array $gamesTurns = array();

    private int $step;
    /*
    * 0 -> need moree infos
    * 1 -> need to populate players
    * 2 -> first round
    */

    private function testStep0To1():void{
        if( $this->step ==0 ){
            if ( isset($this->game_name) 
                 && isset($this->urlpart) 
                 && !empty($this->allowedCreatures)
            )
            {
                $this->step = 1;
            }
        }
    }


    public function set_game_name(string $name):board{
        $this->game_name = $name;
        $this->testStep0To1();
        return $this;
    }
    public function get_game_name():string{
        return $this->game_name;
    }

    public function add_allowedCreature(string $name):board{
        $this->allowedCreatures[] = $name;
        $this->testStep0To1();
        return $this;
    }

    public function set_allowedCreatures(array $creatures): board{
        $this->allowedCreatures = $creatures;
        $this->testStep0To1();
        return $this;
    }

    public function get_allowedCreatures():array{
        return $this->allowedCreatures;
    }

    public function set_urlpart( string $part = ""): Board{
        if(empty($part)){
            $this->urlpart = uniqid();
        }else{
            $this->urlpart = $part;
        }
        $this->testStep0To1();
        return $this();
    }

    public function  geturlpart(): string{
        return $this->urlpart;
    }

    public function __construct(){
        $this->step = 0;
    }


    public function newGameTurn(){


        
    }





}