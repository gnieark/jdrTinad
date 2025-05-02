<?php

class BoardMJ extends Route{

    static public function get_content_html(User $user):string{
        //check group 
        if( !$user->is_in_group("mj") ){
            return C403::get_content_html($user);
        }
        if(  preg_match ( "'^/board/(.+)$'", $_SERVER["REQUEST_URI"], $matches)  ){
            $bordUid = $matches[1];
            $board = Board::loadBoard($bordUid);
            return self::get_content_html_initialized_board($user,$board);


          
        }else{
            return file_get_contents("../templates/board-init.html");
        }   
    }
    static private function get_content_html_initialized_board(User $user, Board $board):string{
        $tpl = new TplBlock();
        $tpl->addVars(
            array(
                "linkgamecontent"  => "<script>createLinkAndQR('" . $board->get_urlpart() ." ');</script>",
                "boarduid"         => $board->get_urlpart(),
                "boardtitle"       => htmlentities($board->get_game_name() )
            )
        );
        return $tpl->applyTplFile("../templates/board.html");
    }
    static public function get_custom_css(User $user):string{
        return file_get_contents ("../templates/board.css");
    }
    static public function get_custom_js():string{

        if(  preg_match ( "'^/board/(.+)$'", $_SERVER["REQUEST_URI"], $matches)  ){
            return file_get_contents ("../templates/board.js");
        }else{
            return file_get_contents ("../templates/board-init.js");
        } 
    }
    
    static public function get_custom_elems_on_header(User $user):string{
        return '<script src="/qrious/qrious.min.js"></script>';
    }

    static public function apply_post(User $user):string{
        if( !$user->is_in_group("mj") ){
            return C403::get_content_html($user);
        }

        if( $_SERVER["REQUEST_URI"] == "/board/init")  { 
            $board = new Board();
            $board->set_game_name( $_POST["game_name"] );

            if( $_POST["custom_url"] == "oui" ){
                $board->set_urlpart( $_POST["custom_url_value"]);
            }else{
                $board->set_urlpart( uniqid() );
            }

            $board->set_allowedCreatures( $_POST["types"] );
            $board->save();
            header('Location: /board/' . $board->get_urlpart() );
        }


        return "";
    }
    
}