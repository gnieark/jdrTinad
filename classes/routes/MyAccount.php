<?php
class MyAccount extends route
{
    static public function get_custom_js():string{
        return file_get_contents("../templates/MyAccount.js");
    }
    static public function get_custom_css(User $user):string{
        return file_get_contents ("../templates/MyAccount.css");
    }

    static public function get_content_html(User $user):string{
        $tpl = new TplBlock();
        if( $user->get_oauth_provider() == 'local' ){
            $tpl->addVars(
                array(
                    "userlogin"         => $user->get_login(),
                    "userdisplayname"   => $user->get_display_name()
            ));
            return $tpl->applyTplFile("../templates/MyAccount-local.html");

        }else{
            $tpl->addVars(
                array(
                    "provider_name"     => $user->get_oauth_provider(),
                    "provider_openid"   => $user->get_oauth_id(),
                    "userdisplayname"   => $user->get_display_name()
                )
            );
            return $tpl->applyTplFile("../templates/MyAccount-oauth.html");
        }


        
    }
    static public function apply_post(User $user):string {
        if( $user->get_oauth_provider() == 'local' ){

            $user ->set_and_save_login( Database::get_db(), $_POST["login"] )
                  ->set_and_save_display_name( Database::get_db(), $_POST["displayname"] );
            
            if( !empty($_POST["password"]) && ($_POST["password"] == $_POST["confirm_password"]) ){
                $user->set_password(Database::get_db(), $_POST["password"] );
            }
        }else{
            //oauth
            $user->set_and_save_display_name( Database::get_db(), $_POST["displayname"] );
        }

        $_SESSION["user"] = serialize($user);
        header('Location: /MyAccount');
        die();
    }
}