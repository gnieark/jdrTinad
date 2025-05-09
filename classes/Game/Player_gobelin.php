<?php
class Player_gobelin extends Player {
    protected static $maxFO = 9;
    protected static $maxINT = 10;
    protected static $maxCHA = 8;
    protected static $maxCOU = 10;
    protected static $maxPV = 20;

    static public function get_origine_desc():string{
        return file_get_contents("../templates/origines/gobelin.txt");
    }
}
