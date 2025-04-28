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

    public function is_the_current_menu_item() :bool
    {
       return (preg_match($this->uriPattern, $_SERVER["REQUEST_URI"]));
    }
    
    public function get_uriPattern() :string
    {
        return $this->uriPatttern;
    }

    public function get_name():string
    {
        return $this->name;
    }

    public function set_link(string $link): MenuItem{$this->link = $link; return $this;}
    public function get_link():string {return $this->link;}

    public function is_user_allowed(User $user):bool
    {
        if ($this->levelNeeded == 'user' && $user->is_authentified())
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

    private function test_scrudClass(string $className):bool
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

    public function __construct(string $name, string $shortName, string $levelNeeded, string $scrudClass, string $uriPattern , string $link = null, bool $display_on_nav = false, array $groups_allowed = array())
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
   
    public function apply_post(User $user):string {
       return call_user_func_array( array( $this->scrudClass, 'apply_post'), array($user));
    }
    public function apply_delete( User $user): string{
        return call_user_func_array( array( $this->scrudClass, 'apply_delete'), array($user));
    }
    public function apply_patch(User $user): string {
        return call_user_func_array( array( $this->scrudClass, 'apply_patch'), array($user));
    }
    public function get_custom_js(User $user): string {
        return call_user_func_array( array( $this->scrudClass, 'get_custom_js'), array($user));
    }
    public function get_custom_css(User $user): string {
        return call_user_func_array( array( $this->scrudClass, 'get_custom_css'), array($user));
    }
    public function get_content_html(User $user): string {
        return call_user_func_array( array( $this->scrudClass, 'get_content_html'), array($user));
    }
    public function display_on_page():bool {
        return call_user_func_array( array( $this->scrudClass, 'display_on_page'),array());
    }
    public function get_custom_after_body_tag(User $user): string{
        return call_user_func_array( array( $this->scrudClass, 'get_custom_after_body_tag'), array($user));
    }
    public function send_content(User $user): string {
        return call_user_func_array( array( $this->scrudClass, 'send_content'), array($user));
    }
    //
    public function get_custom_elems_on_header(User $user): string {
        return call_user_func_array( array( $this->scrudClass, 'get_custom_elems_on_header'), array($user));
    }
}