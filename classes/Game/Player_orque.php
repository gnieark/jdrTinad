<?php
class Player_orque extends Player {
    protected static $minFO = 12;
    protected static $maxINT = 8;
    protected static $maxCHA = 10;
    protected static $maxPV = 35;

    static public function get_origine_desc():string{
        return file_get_contents("../templates/origines/orque.txt");
    }
}
