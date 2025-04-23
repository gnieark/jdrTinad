<?php
class TestMenusClass{
    static public function get_custom_js(User $user) :string{
        return "";

    }
    static public function get_custom_css(User $user) :string{
        return "";
    }
    static public function get_content_html(User $user) :string{

        return "";
    }
    static public function apply_post(User $user) :string{
        return ""; 
    }


}
class TestMenusClass1 extends TestMenusClass{

}
class TestMenusClass2 extends TestMenusClass{

}
class TestMenusClass3 extends TestMenusClass{


}

class TestMenusClassUnconsistend{


}