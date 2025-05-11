<?php
class GodFatherLink extends Route{
    static public function get_content_html(User $user):string{
        $tpl = new TplBlock();
        return $tpl->applyTplFile("../templates/godfather.html");
    }
    static public function get_custom_css(User $user):string{
        return file_get_contents ("../templates/godfather.css");
    }

}