<?php
class Player_semi_homme extends Player {
    protected static $origine_title = "semi-homme";
    protected static $minCOU = 12;
    protected static $minINT = 10;
    protected static $maxFO = 10;
    protected static $maxPV = 25;

    static public function get_origine_desc():string{
        return file_get_contents("../templates/origines/semi-homme.txt");
    }
}
