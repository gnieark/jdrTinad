<?php
class Player_demi_orque extends Player {
    protected static $origine_title = "demi-orque";
    protected static $minFO = 12;
    protected static $maxINT = 10;
    protected static $maxAD = 11;
    protected static $maxPV = 35;

    static public function get_origine_desc():string{
        return file_get_contents("../templates/origines/demi-orque.txt");
    }
}
