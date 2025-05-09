<?php
class Player_demi_elfe extends Player {
    protected static $origine_title = "demi-elfe";
    protected static $minCHA = 10;
    protected static $minAD = 11;
    protected static $maxPV = 28;

    static public function get_origine_desc():string{
        return file_get_contents("../templates/origines/demi-elfe.txt");
    }
}
