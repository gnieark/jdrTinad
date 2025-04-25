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
    static public function apply_post(User $user):string{
        //print_r($_POST); Array ( [login] => ezye(rurtei [password] => etyuikyiom )
        $user->authentificate(Database::get_db(), $_POST["login"], $_POST["password"]);

        if($user->is_authentified()){
            $_SESSION["user"] = serialize($user);
            header('Location: /');
            die();
        }
        //else do nothing
        return "";
    }
}