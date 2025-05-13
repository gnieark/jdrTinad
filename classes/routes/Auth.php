<?php
use League\OAuth2\Client\Provider\Google;
class Auth extends Route{
    static public function get_content_html(User $user):string{
        if(preg_match("~^/auth/provider/([^/?]+)~", $_SERVER["REQUEST_URI"], $matches)){
            $providername = $matches[1];
            switch($providername){
                case "google":
                    $providers = json_decode(file_get_contents("../config/oauth.json"), true);
                    $gProvider = $providers["google"];
                    $provider = new Google([
                        'clientId'     => $gProvider["web"]["client_id"],
                        'clientSecret' => $gProvider["web"]["client_secret"],
                        'redirectUri'  => 'https://jdr.tinad.fr/auth/provider/google',
                        'scope'        => ['openid'] 
                    ]);

                    break;
                default:
                 return C404::get_content_html($user);
                 break;
            }
            if (!isset($_GET['code'])) {
                // Redirection vers le provider
                $authUrl = $provider->getAuthorizationUrl();
                $_SESSION['oauth2state'] = $provider->getState();
                header('Location: ' . $authUrl);
                exit;
            } else {
                // Callback
                try {
                    $token = $provider->getAccessToken('authorization_code', [
                        'code' => $_GET['code']
                    ]);
            
                    $resourceOwner = $provider->getResourceOwner($token);
         
                    $user = new User();
                    $user->authentificated_oauth(Database::get_db(), "google", $resourceOwner->getId() );
                    if( $user-> is_authentified() ){
                        $_SESSION["user"] = serialize($user);
                        header('Location: /');
                        return "";
                    }else{
                        header('Location: /auth');
                        return "";
                    }
        


                } catch (\Exception $e) {
                    return ('Erreur OAuth: ' . $e->getMessage());
                }
            }

        }else{
            return file_get_contents ("../templates/auth.html");

        }
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