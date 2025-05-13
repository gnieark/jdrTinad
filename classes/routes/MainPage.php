<?php
class MainPage extends Route{
    static public function get_content_html(User $user):string{
        return '
<div class="logo-container">
  <img src="/logo.png" alt="Logo jdr.tinad.fr" class="site-logo">
</div>';
    }
    
}