<?php
class Player_haut_elfe extends Player {
    protected static $minCHA = 12;
    protected static $minAD = 12;
    protected static $minINT = 11;
    protected static $maxFO = 12;
    protected static $maxPV = 25;

    static public function get_origine_desc():string{
        return file_get_contents("../templates/origines/haut-elfe.txt");
    }
}
