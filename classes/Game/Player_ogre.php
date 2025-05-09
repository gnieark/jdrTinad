<?php
class Player_ogre extends Player {
    protected static $minFO = 13;
    protected static $maxINT = 9;
    protected static $maxCHA = 10;
    protected static $maxAD = 11;
    protected static $maxPV = 45;

    static public function get_origine_desc():string{
        return file_get_contents("../templates/origines/ogre.txt");
    }
}
