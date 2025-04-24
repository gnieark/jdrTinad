<?php
class Auth extends Route{
    static public function get_content_html(User $user):string{
        return file_get_contents ("../templates/auth.html");
    }
    static public function get_custom_css(User $user):string{
        return file_get_contents ("../templates/auth.css");
    }
    static public function get_custom_js():string{
        return file_get_contents ("../templates/auth.js");
    }
    
}