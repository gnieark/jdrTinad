<?php
class Player_elfe_noir extends Player {
    protected static $minAD = 13;
    protected static $minINT = 12;
    protected static $maxFO = 12;
    protected static $maxPV = 25;

    static public function get_origine_desc():string{
        return file_get_contents("../templates/origines/elfe-noir.txt");
    }
}
