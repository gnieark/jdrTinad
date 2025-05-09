<?php
class Player_humain extends Player {
    protected static $origine_title = "humain";
    protected static $maxPV = 30;

    static public function get_origine_desc():string{
        return file_get_contents("../templates/origines/humain.txt");
    }
}
