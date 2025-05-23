<?php

class C403 extends Route{
    static public function get_content_html(User $user):string{
        header("HTTP/1.0 403 Forbidden");
        return file_get_contents ("../templates/403.html");
    }
    static public function send_content(User $user):string
    {
        header("HTTP/1.0 403 Forbidden");
        echo $tpl->applyTplFile("../templates/403.html");
    }
    static public function get_custom_css(User $user):string{
        return file_get_contents ("../templates/404.css");
    }
    static public function send_content_json(): void
    {
        http_response_code(403);
        header('Content-Type: application/json');

        echo json_encode([
            'error' => 'Forbidden',
            'message' => 'Vous n\'avez pas les droits pour accéder à cette ressource.'
        ]);
        die();
}

    
}