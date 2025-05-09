<?php
class Player_barbare extends Player {
    protected static $origine_title = "barbare";
    protected static $minFO = 13;
    protected static $minCOU = 12;
    protected static $maxPV = 35;

    static public function get_origine_desc():string{
        return file_get_contents("../templates/origines/barbare.txt");
    }
}
