<?php
class Player_gnome_des_forets_du_nord extends Player {
    protected static $minAD = 13;
    protected static $minINT = 10;
    protected static $maxFO = 8;
    protected static $maxPV = 15;

    static public function get_origine_desc():string{
        return file_get_contents("../templates/origines/gnome-des-forets-du-nord.txt");
    }
}
