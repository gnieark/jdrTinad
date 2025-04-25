<?php

class BoardMJ extends Route{

    static public function get_content_html(User $user):string{
        return file_get_contents ("../templates/board.html");
    }
    static public function get_custom_css(User $user):string{
        return file_get_contents ("../templates/board.css");
    }
    static public function get_custom_js():string{
        return file_get_contents ("../templates/board.js");
    }
    static public function apply_post(User $user):string{


    }
    
}