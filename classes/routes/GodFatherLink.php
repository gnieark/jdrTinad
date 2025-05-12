<?php
class GodFatherLink extends Route{
    static public function get_content_html(User $user):string{

        preg_match ( "'^/godfatherlink/(.+)'", $_SERVER["REQUEST_URI"], $matches);
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
    static public function get_custom_css(User $user):string{
        return file_get_contents ("../templates/godfather.css");
    }

}