<?php
class Route
{
    protected static $displayOnPage = true; //if not, get_content_html() result should be send before all headers

    public static function set_displayOnPage(bool $value)
    {
        static::$displayOnPage = $value;
    }

    static public function display401PageIfUnathorized(PDO $db, User $user)
    {
        if( self::is_user_allowed($user, static::get_allowed_groups($db)) === false )
        {
            C401::set_displayOnPage(true);
            return C401::send_content($db,$user);
            
        }
        return false;
    } 
    static public function get_allowed_groups(PDO $db)
    {
        return array();
    }

    static public function is_user_allowed(User $user, $allowedGroupsIds)
    {
        foreach($user->get_groups() as $ugroup){
            if(in_array( $ugroup,$allowedGroupsIds )){
                return true;
            }
        }
        return false;
    }
    

    static public function display_on_page(){
        return static::$displayOnPage;
    }
    static public function get_custom_js()
    {
        return "";
    }
    static public function get_custom_css(PDO $db, User $user)
    {
        return "";
    }
    static public function get_content_html(PDO $db, User $user)
    {
        return "";
    }
    static public function get_custom_after_body_tag(PDO $db, User $user)
    {
        return "";
    }
    static public function apply_post(PDO $db, User $user)
    {
        return "";
    }
    static public function apply_delete(PDO $db, User $user)
    {
        return "";
    }
    static public function apply_patch(PDO $db, User $user)
    {
        return "";
    }
    static public function send_content(PDO $db, User $user)
    {
        return "";
    }

}
