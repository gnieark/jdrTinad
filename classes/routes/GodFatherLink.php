<?php
use League\OAuth2\Client\Provider\Google;

class GodFatherLink extends Route{
    static public function get_content_html(User $user):string{
        if (preg_match ( "'^/godfatherlink/callback/google'", $_SERVER["REQUEST_URI"], $matches)){

            if (!isset($_GET['state'])) {
                return("State manquant.");
            }
            
            $stateData = json_decode(base64_decode($_GET['state']), true);
            // Vérifie le token CSRF
            if (!isset($stateData['csrf']) || $stateData['csrf'] !== ($_SESSION['oauth2state'] ?? '')) {
                return("Erreur de sécurité (state mismatch)");
            }

            $linkUid = $stateData['linkuid'] ?? null;

            if (!$linkUid) {
                return("Aucun linkUid transmis.");
            }


            $providers = json_decode(file_get_contents("../config/oauth.json"), true);
            $gProvider = $providers["google"];
            $provider = new Google([
                'clientId'     => $gProvider["web"]["client_id"],
                'clientSecret' => $gProvider["web"]["client_secret"],
                'redirectUri'  => 'https://jdr.tinad.fr/godfatherlink/callback/google',
                'scope'        => ['openid'] 
            ]);


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

            //for check later
            $_SESSION["verifiedoauth"] = array("sub"    => $sub, "provider" => "google");

            //TO do vérifier si le compte n'existerait pas déjà
            $testUser = new User();
            $testUser->authentificated_oauth(Database::get_db(), "google", $sub);
            if( $testUser-> is_authentified() ){
                return "Erreur: Ce compte Google est déjà utilisé";
            }


            $tpl = new TplBlock();
            $tpl->addVars(
                array(
                    "oauth_id"          => $sub,
                    "godfatherlinkuid"  => $linkUid
                )
            );

            
            return $tpl->applyTplFile("../templates/godfather-choosepseudo.html");
            return $sub;
    
        }elseif (preg_match ( "'^/godfatherlink/(.+)/provider/(.+)$'", $_SERVER["REQUEST_URI"], $matches)){
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
                        'redirectUri'  => 'https://jdr.tinad.fr/godfatherlink/callback/google',
                        'scope'        => ['openid'] 
                    ]);

                    
                    break;

                default:
                    return C404::get_content_html($user);
                    break;

            }
                // Génère une valeur `state` contenant le linkUid
            $statePayload = [
                'linkuid' => $linkUid,
                'csrf' => bin2hex(random_bytes(16))
            ];
            $encodedState = base64_encode(json_encode($statePayload));
            $_SESSION['oauth2state'] = $statePayload['csrf'];

            // Redirection vers le provider
            $authUrl = $provider->getAuthorizationUrl(['state' => $encodedState]);
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
        }else{
            return C404::send_content($user);
        }
    }
    static public function get_custom_css(User $user):string{
        return file_get_contents ("../templates/godfather.css");
    }
    static public function apply_post(User $user):string {
        if ($_SESSION["verifiedoauth"]["sub"] !== $_POST["oauth_id"] ){
            return C403::send_content($user);
        }

        $mjGroup = UserGroupManager::get_group_by_name(Database::get_db(), 'mj' );
        $user = UserGroupManager::createOauthUser(Database::get_db(), 
                                                        $_POST["pseudo"],
                                                        $_SESSION["verifiedoauth"]["provider"] ,
                                                        $_SESSION["verifiedoauth"]["sub"],
                                                        array( $mjGroup->get_id() )
                                                        
        );

        //delete link!
        $link = ProposingLink::load_link_by_uid(Database::get_db(),$_POST["godfatherlinkuid"]);
        $link->delete( Database::get_db() );


        $_SESSION["user"] = serialize($user);
        header('Location: /');
        die();
        return "";
    }

}