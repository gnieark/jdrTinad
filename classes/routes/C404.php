<?php

class C404 extends Route{
    static public function get_content_html(User $user):string{
        header("HTTP/1.0 404 Not Found");
        return file_get_contents ("../templates/404.html");
    }
    static public function send_content(User $user):string
    {
        header("HTTP/1.0 404 Not Found");
        echo $tpl->applyTplFile("../templates/404.html");
    }
    static public function get_custom_css(User $user):string{
        return file_get_contents ("../templates/404.css");
    }
    static public function send_content_json():void
    {
        http_response_code(404);
        header('Content-Type: application/json');
    
        echo json_encode([
            'error' => 'Not Found',
            'message' => 'La ressource demandée est introuvable.'
        ]);
        die();
 
    }
}