<?php
use League\CommonMark\CommonMarkConverter;

class About extends Route{
    static public function get_content_html(User $user):string{

        $converter = new CommonMarkConverter();
        return $converter->convertToHtml( file_get_contents("../templates/About.md")   );

    }

    static public function get_custom_css(User $user):string{
        return file_get_contents ("../templates/tutos.css");
    }


}