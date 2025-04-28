<?php

class BoardMJ extends Route{

    static public function get_content_html(User $user):string{
        //check group 
        if( !$user->is_in_group("mj") ){
            return C403::get_content_html($user);
        }
        return file_get_contents ("../templates/board.html");
    }
    static public function get_custom_css(User $user):string{
        return file_get_contents ("../templates/board.css");
    }
    static public function get_custom_js():string{
        return file_get_contents ("../templates/board.js");
    }
    static public function apply_post(User $user):string{
        if( !$user->is_in_group("mj") ){
            return C403::get_content_html($user);
        }

        if( $_SERVER["REQUEST_URI"] == "/board/init")  {
            //print_r($_POST); Array ( [game_name] => erturtuyi [prompt] => uyytiuo [custom_url] => non [custom_url_value] => [types] => Array ( [0] => nain [1] => elfe [2] => barbare [3] => humain [4] => ogre [5] => gobelin [6] => demi-elfe ) )

            $board = new Board();
            $board->set_game_name( $_POST["game_name"] );

            if( $_POST["custom_url"] == "oui" ){
                $board->set_urlpart( $_POST["custom_url_value"]);
            }else{
                $board->set_urlpart( uniqid() );
            }

            $board->set_allowedCreatures( $_POST["types"] );


        }

    }
    
}