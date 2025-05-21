<?php

use League\CommonMark\CommonMarkConverter;

class Tutos extends Route{
    static public function get_content_html(User $user):string{

        $converter = new CommonMarkConverter();

        if($_SERVER["REQUEST_URI"] == "/tutos/mj"){
            return $converter->convertToHtml( file_get_contents("../templates/tutos/mj.md")   );
        }

        if($_SERVER["REQUEST_URI"] == "/tutos/aventurier"){
            return $converter->convertToHtml( file_get_contents("../templates/tutos/aventurier.md")   );
        }

        if($_SERVER["REQUEST_URI"] == "/tutos/mecaniques"){
            return $converter->convertToHtml( file_get_contents("../templates/tutos/mecaniques.md")   );
        }

        return C404::get_content_html($user);
    }

    static public function get_custom_css(User $user):string{
        return file_get_contents ("../templates/tutos.css");
    }

}