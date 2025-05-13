<?php
use League\OAuth2\Client\Provider\Google;
use Wohali\OAuth2\Client\Provider\Discord;
class Auth extends Route{
    static public function get_content_html(User $user):string{
        if(preg_match("~^/auth/provider/([^/?]+)~", $_SERVER["REQUEST_URI"], $matches)){
            $providername = $matches[1];
            $providers = json_decode(file_get_contents("../config/oauth.json"), true);

            switch($providername){
                case "google":
                    $gProvider = $providers["google"];
                    $provider = new Google([
                        'clientId'     => $gProvider["web"]["client_id"],
                        'clientSecret' => $gProvider["web"]["client_secret"],
                        'redirectUri'  => 'https://jdr.tinad.fr/auth/provider/google',
                        'scope'        => ['openid'] 
                    ]);
                    break;
                case "discord":
                    $gProvider = $providers["discord"];
                    $provider = new Discord([
                        'clientId'     => $gProvider["web"]["client_id"],
                        'clientSecret' => $gProvider["web"]["client_secret"],
                        'redirectUri'  => 'https://jdr.tinad.fr/auth/provider/discord'
                    ]);
                    break;
                default:
                 return C404::get_content_html($user);
                 break;
            }
            if (!isset($_GET['code'])) {

                if( $providername == "discord"){
                    $options = array(
                        "scope" => array("openid")
                    );
                    $authUrl = $provider->getAuthorizationUrl( $options );
                }else{
                    $authUrl = $provider->getAuthorizationUrl();
                }
                
                $_SESSION['oauth2state'] = $provider->getState();

                header('Location: ' . $authUrl);
                exit;

            } else {
                // Callback
                //try {

                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);
                
                $idToken = $token->getValues()['id_token'] ?? null;
                
                if (!$idToken) {
                    return("Aucun id_token fourni.");
                }
                
                $parts = explode('.', $idToken);
                $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                $sub = $payload['sub'] ?? null;
                
                if (!$sub) {
                    return("Identifiant OpenID non trouvé.");
                }
    
                $user = new User();
                $user->authentificated_oauth(Database::get_db(), $providername, $sub  );
                if( $user-> is_authentified() ){
                    $_SESSION["user"] = serialize($user);
                    header('Location: /');
                    return "";
                }else{
                    header('Location: /auth');
                    return "";
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