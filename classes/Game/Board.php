<?php

class Board{
    private string $game_name;
    private array $allowedCreatures = array();
    private string $urlpart;
    private array $playTurns = array();

    private string $saveUid;

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

    public function get_saveUid():string{
        return $this->saveUid;
    }
    public function add_playTurn ( PlayTurn $playTurn): Board{
        $this->playTurns[] = $playTurn;
        return $this;
    }
    public function get_playTurns():array{
        return $this->playTurns;
    }
    
    public function set_game_name(string $name):Board{
        $this->game_name = $name;
        $this->testStep0To1();
        return $this;
    }
    public function get_game_name():string{
        return $this->game_name;
    }

    public function add_allowedCreature(string $name):Board{
        $this->allowedCreatures[] = $name;
        $this->testStep0To1();
        return $this;
    }

    public function set_allowedCreatures(array $creatures): Board{
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
    public function get_save_real_path():string{
        return realpath("../gamesdatas/" . $this->urlpart);
    }

    public function get_urlpart(): string{
        return $this->urlpart;
    }

    public function __construct(){
        $this->step = 0;
    }


    public function newGameTurn(){
        
    }
    public function get_players() : array{
        $players = [];

        $folderPath = "../gamesdatas/" . $this->urlpart . "/";
        if (!is_dir($folderPath)) {
            return $players;
        }
    
        $files = scandir($folderPath);
        foreach ($files as $file) {
            if (str_starts_with($file, "player-")) {
                $filePath = $folderPath . $file;
                $players[] = Player::loadPlayer($filePath);
            }
        }
    
        return $players;
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
        $this->saveUid = uniqid();
        // Écrire dans le fichier
        $path = $folderPath . "/board.txt";
        file_put_contents($path, serialize($this));
    
        return $this;
    }
    public static function boardFileExists(string $urlPart):bool{
        $path = "../gamesdatas/" . $urlPart . "/board.txt";
        return file_exists($path);
    }
    public static function loadBoard(string $urlPart):Board{
        $path = "../gamesdatas/" . $urlPart . "/board.txt";

        if (!file_exists($path)) {
            throw new Exception("Cannot load: file not found at $path.");
        }
    
        $data = file_get_contents($path);
        return unserialize($data);        
    }



}