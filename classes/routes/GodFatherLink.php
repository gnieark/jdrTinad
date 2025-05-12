<?php
use League\OAuth2\Client\Provider\Google;

class GodFatherLink extends Route{
    static public function get_content_html(User $user):string{
        if (preg_match ( "'^/godfatherlink/(.+)/provider/(.+)$'", $_SERVER["REQUEST_URI"], $matches)){
            $linkUid = $matches[1];
            $provider = $matches[2];

            if(! $proposinglLink = ProposingLink::load_link_by_uid(Database::get_db(), $linkUid)){
                return C404::get_content_html($user);
            }

 
            $providers = json_decode(file_get_contents("../config/oauth.json"), true);
            switch( $provider ){
                case "google":
                    
                    $gProvider = $providers["google"];
                    $provider = new Google([
                        'clientId'     => $gProvider["web"]["client_id"],
                        'clientSecret' => $gProvider["web"]["client_secret"],
                        'redirectUri'  => 'https://jdr.tinad.fr/oauth/callback/google',
                    ]);

                    
                    break;

                default:
                    return C404::get_content_html($user);
                    break;

            }
            // Redirection vers le provider
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: ' . $authUrl);
            exit;

        }elseif( preg_match ( "'^/godfatherlink/(.+)'", $_SERVER["REQUEST_URI"], $matches) ){

            $linkUid = $matches[1];
            if(! $proposinglLink = ProposingLink::load_link_by_uid(Database::get_db(), $linkUid)){
                return C404::get_content_html($user);
            }

            //get_godfather_uid
            $godfather = new User();
            $godfather->set_id(  $proposinglLink->get_godfather_uid() )->load_from_db(Database::get_db());

            $tpl = new TplBlock();
            $tpl->addVars(
                array(
                    "nom_du_mj"         => $godfather->get_display_name(),
                    "linkgoogleauth"    => "/godfatherlink/" . $linkUid . "/provider/google",
                    "linkdiscordauth"    => "/godfatherlink/" . $linkUid . "/provider/discord"
                )
            );

            return $tpl->applyTplFile("../templates/godfather.html");
        }
    }
    static public function get_custom_css(User $user):string{
        return file_get_contents ("../templates/godfather.css");
    }

}