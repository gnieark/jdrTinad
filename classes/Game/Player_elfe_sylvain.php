<?php
class Player_elfe_sylvain extends Player {
    protected static $origine_title = "elfe-sylvain";
    protected static $minCHA = 12;
    protected static $minAD = 10;
    protected static $maxFO = 11;
    protected static $maxPV = 25;

    static public function get_origine_desc():string{
        return file_get_contents("../templates/origines/elfe-sylvain.txt");
    }
}
