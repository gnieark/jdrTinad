<?php
class GodFatherLink extends Route{
    static public function get_content_html(User $user):string{
        if (preg_match ( "'^/godfatherlink/(.+)/provider/(.+)$'", $_SERVER["REQUEST_URI"], $matches)){
            $linkUid = $matches[1];
            $provider = $matches[2];

            if(! $proposinglLink = ProposingLink::load_link_by_uid(Database::get_db(), $linkUid)){
                return C404::get_content_html($user);
            }

            //inclide oauth lib files
            $pathOuthLibs = $realPath("../classes/oauth2-client/src");
            $subDirs = array_filter(glob($pathOuthLibs . '/*'), 'is_dir');
            foreach($subDirs as $subDir){
                foreach (glob($subDir . '/*.php') as $phpFile) {
                    require_once $phpFile;
                }
            }

            switch( $provider ){
                case "google":
                    $provider = new Google([
                        'clientId'     => 'XXX',
                        'clientSecret' => 'XXX',
                        'redirectUri'  => 'https://jdr.tinad.fr/oauth/callback/google',
                    ]);

                    
                    break;

                default:
                    return C404::get_content_html($user);
                    break;

            }


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
                    "nom_du_mj"  => $godfather->get_display_name()
                )
            );

            return $tpl->applyTplFile("../templates/godfather.html");
        }
    }
    static public function get_custom_css(User $user):string{
        return file_get_contents ("../templates/godfather.css");
    }

}