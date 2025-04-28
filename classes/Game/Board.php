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
            $newurlpart = uniqid();
        }else{
            $newurlpart = $part;
        }

        if(isset($this->urlpart)){
            //move the folder
            rename("../gamesdatas/" . $this->urlpart, "../gamesdatas/" . $newurlpart );
        }else{
            //create the folder
            mkdir("../gamesdatas/" . $newurlpart, 0700);
        }

        $this->urlpart = $newurlpart;

        $this->testStep0To1();
        return $this;
    }

    public function  get_urlpart(): string{
        return $this->urlpart;
    }

    public function __construct(){
        $this->step = 0;
    }


    public function newGameTurn(){
        


    }
    public function save(): Board
    {
        if (empty($this->urlpart)) {
            throw new Exception("Cannot save: urlpart is not set.");
        }
    
        // S'assurer que le dossier existe
        $folderPath = "../gamesdatas/" . $this->urlpart;
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0700, true);
        }
    
        // Sérialiser avec msgpack
        $serialized = msgpack_pack($this);
    
        // Écrire dans le fichier
        $path = $folderPath . "/board.bin";
        file_put_contents($path, $serialized);
    
        return $this;
    }

    public static function loadBoard(string $urlPart):Board{
        $path = "../gamesdatas/" . $urlPart . "/board.bin";

        if (!file_exists($path)) {
            throw new Exception("Cannot load: file not found at $path.");
        }
    
        $data = file_get_contents($path);
        $unpacked = msgpack_unpack($data);
    
        if (!is_array($unpacked)) {
            throw new Exception("Cannot load: corrupted data.");
        }
    
        $board = new Board();
        $board->__unserialize($unpacked);
    
        return $board;
        
    }



}