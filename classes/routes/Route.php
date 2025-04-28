<?php
class Route
{
    protected static $displayOnPage = true; //if not, get_content_html() result should be send before all headers

    public static function set_displayOnPage(bool $value):void
    {
        static::$displayOnPage = $value;
    }

    static public function display_on_page():bool{
        return static::$displayOnPage;
    }
    static public function get_custom_js():string{
        return "";
    }
    static public function get_custom_css(User $user):string{
        return "";
    }
    static public function get_content_html(User $user):string{
        return "";
    }
    static public function get_custom_after_body_tag(User $user):string{
        return "";
    }
    static public function apply_post(User $user):string{
        return "";
    }
    static public function apply_delete(User $user):string{
        return "";
    }
    static public function apply_patch(User $user):string{
        return "";
    }
    static public function send_content(User $user):string{
        return "";
    }
    static public function get_custom_elems_on_header(User $user):string{
        return "";
    }
}
