<?php
class MenuItem
{
    private $name = NULL;
    public $shortName = NULL;
    private $levelNeeded = 'admin';
    private $groups_allowed = array();
    private $uriPattern = NULL;
    private $link = NULL;
    public $scrudClass = NULL;
    public $displayOnNav = false;

    public function is_the_current_menu_item()
    {
       return (preg_match($this->uriPattern, $_SERVER["REQUEST_URI"]));
    }
    
    public function get_uriPattern()
    {
        return $this->uriPatttern;
    }

    public function get_name()
    {
        return $this->name;
    }

    public function set_link($link){$this->link = $link; return $this;}
    public function get_link(){return $this->link;}

    public function is_user_allowed(User $user)
    {

        if( $user->is_admin() )
            return true;
        if ($this->levelNeeded == 'user' && $user->is_connected())
        {
            if(empty($this->groups_allowed)){
                return true;
            }
            foreach($this->groups_allowed as $group_allowed){
                if($user->is_in_group($group_allowed)){
                    return true;
                }
            }
            return false;  
        }   
        if( $this->levelNeeded == 'guest')
            return true;
        return false;
    }

    private function test_scrudClass($className)
    {
        $methodsNeeded = array(
                            'get_custom_js',
                            'get_custom_css',
                            'get_content_html',
                            'apply_post'
                        );

        if( !class_exists($className) )
        {
            return false;
        }
        foreach( $methodsNeeded as $method )
        {
            if( !method_exists( $className, $method ) )
            {
                return false;
            }
        }
        return true;
    }

    public function __construct($name, $shortName, $levelNeeded,$scrudClass, $uriPattern , $link = null, $display_on_nav = false, $groups_allowed = array())
    {
        $this->name = $name;
        $this->shortName = $shortName;
        $this->uriPattern = $uriPattern; 
        $this->link = $link;

        if(!in_array($levelNeeded, array('user','admin','guest'))){
            throw new \UnexpectedValueException(
                "third parameter must be 'admin' or 'user'. " . $levelNeeded . " given."
            );
        }
        if(!$this->test_scrudClass($scrudClass ))
        {
            throw new \UnexpectedValueException(
                "Class " . $scrudClass . " does not exists or doesnot have expected methods"
            );
        }

        $this->levelNeeded = $levelNeeded;
        $this->scrudClass = $scrudClass;
        $this->displayOnNav = $display_on_nav;
        $this->groups_allowed = $groups_allowed;
    }
   
    public function apply_post(PDO $db, User $user){
       return call_user_func_array( array( $this->scrudClass, 'apply_post'), array($db,$user));
    }
    public function apply_delete(PDO $db, User $user){
        return call_user_func_array( array( $this->scrudClass, 'apply_delete'), array($db,$user));
    }
    public function apply_patch(PDO $db, User $user){
        return call_user_func_array( array( $this->scrudClass, 'apply_patch'), array($db,$user));
    }
    public function get_custom_js(PDO $db, User $user){
        return call_user_func_array( array( $this->scrudClass, 'get_custom_js'), array($db,$user));
    }
    public function get_custom_css(PDO $db, User $user){
        return call_user_func_array( array( $this->scrudClass, 'get_custom_css'), array($db,$user));
    }
    public function get_content_html(PDO $db, User $user){
        return call_user_func_array( array( $this->scrudClass, 'get_content_html'), array($db,$user));
    }
    public function display_on_page(){
        return call_user_func_array( array( $this->scrudClass, 'display_on_page'),array());
    }
    public function get_custom_after_body_tag(PDO $db, User $user){
        return call_user_func_array( array( $this->scrudClass, 'get_custom_after_body_tag'), array($db,$user));
    }
    public function send_content(PDO $db, User $user){
        return call_user_func_array( array( $this->scrudClass, 'send_content'), array($db,$user));
    }   
}