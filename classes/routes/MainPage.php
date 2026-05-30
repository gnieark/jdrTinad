<?php
class MainPage extends Route{
    static public function get_content_html(User $user):string{
      $boardUrlpart = BoardPlayer::get_board_urlpart_from_existing_cookie();
      if($boardUrlpart !== null){
        header('Location: /' . $boardUrlpart);
        die();
      }

      return file_get_contents ("../templates/MainPage.html");
    }

    static public function get_custom_css(User $user):string{
      return file_get_contents ("../templates/MainPage.css");
    }
    
}
