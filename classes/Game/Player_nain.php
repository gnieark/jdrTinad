<?php
class Player_nain extends Player {
    protected static $origine_title = "nain";
    protected static $minFO = 12;
    protected static $minCOU = 11;
    protected static $maxPV = 35;

    static public function get_origine_desc():string{
        return file_get_contents("../templates/origines/nain.txt");
    }
}
