<?php

class BoardPlayer extends Route{

    static public function get_custom_css(User $user):string{
        return file_get_contents ("../templates/playerBoard.css");
    }
    static public function get_custom_js():string{
        return file_get_contents ("../templates/playerBoard.js");
    }

    static private function get_uid_from_cookie():string{
        if( !isset($_COOKIE["jdr_uid"]) ){
            $cookie_name = "jdr_uid";
            $cookie_value = uniqid();
            $cookie_duration = time() + (30 * 24 * 60 * 60); // 30 jours
            $cookie_path = '/'; 

            setcookie($cookie_name, $cookie_value, [
                'expires' => $cookie_duration,
                'path' => $cookie_path,
                'secure' => isset($_SERVER['HTTPS']), 
                'httponly' => true, 
                'samesite' => 'Lax' 
            ]);
            return $cookie_value;
        }else{
            return $_COOKIE["jdr_uid"];
        }
    }

    static public function get_content_html(User $user):string{
        if(preg_match ( "'^/(.+)$'" , $_SERVER["REQUEST_URI"], $matches)){
            $urlpart = $matches[1];
        }else{
            return C404::get_content_html($user);
        }

        if(!Board::boardFileExists($urlpart)){
            return C404::get_content_html($user);
        }
        $board = Board::loadBoard($urlpart);
        $savePath = $board->get_save_real_path();

        if(!file_exists($savePath ."/player-" . self::get_uid_from_cookie() )){
            //joueur non initialisé
            return self::get_content_html_new_player($user);
        }
        return "hey!";
    }

    static public function get_content_html_new_player(User $user):string{
        $tpl = new TplBlock();


        return $tpl->applyTplFile("../templates/playerBoard-init.html");
    }


}