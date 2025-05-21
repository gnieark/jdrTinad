<?php
class MainPage extends Route{
    static public function get_content_html(User $user):string{
        return '
<div class="logo-container">
  <img src="/logo.png" alt="Logo jdr.tinad.fr" class="site-logo">
</div>
<div class="tutoslinks" ><ul><li><a href="/tutos/mecaniques">Mécaniques d\'un JDR (dés, compétences...)</a></li>
<li><a href="/tutos/aventurier">Tutoriel de l\'aventurier</a></li><li><a href="/tutos/mj">Tutoriel du maître du jeu</a></li></ul></div>';
    }

    static public function get_custom_css(User $user):string{
      return file_get_contents ("../templates/MainPage.css");
    }
    
}