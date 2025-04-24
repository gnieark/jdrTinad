<?php

use PHPUnit\Framework\TestCase;

class MenusTest extends TestCase {

    protected PDO $pdo;


    protected function setUp(): void {
        require_once(   dirname(__FILE__) .  "/TestMenusClass.php" );
        include( dirname(__FILE__) .  "/../../users/autoloadUsers.php" );
    }

    public function testLoadMenusDefinitions():void{
        $_SERVER["REQUEST_URI"] = "/kdsjhrgtkrgy";
        $mManager = new Menus_manager();
        $mManager->add_menus_items_from_json_file( dirname(__FILE__) . "/menustests.json");
        $currentMenu = $mManager->get_current_menu();
        //should be 404 menu
        $this->assertEquals("404", $currentMenu->get_name() );
        $_SERVER["REQUEST_URI"] = "/config/kjshgdtug";
        $currentMenu = $mManager->get_current_menu();
        $this->assertEquals("Configuration", $currentMenu->get_name() );
        $_SERVER["REQUEST_URI"] = "/config";
        $currentMenu = $mManager->get_current_menu();
        $this->assertEquals("Configuration", $currentMenu->get_name() );
    }
    public function testInvalidMenus():void{
        $this->expectException(UnexpectedValueException::class);
        $mManager = new Menus_manager();

        $mManager->add_menus_items_from_structured_array(
            array(
                "configMenu"    => array(
                    "name"                      => "Configuration",
                    "default_level_needed"      => "user",
                    "groups_allowed"            => array( 1,2 ),
                    "CRUDclass"                 => "NonExistingClass",
                    "uriPattern"                => "'^/config(/.*)?$'",
                    "link"                      => "/config"
                )
            )
        );
    }
    public function testInvalidMenusMethods():void{
        $this->expectException(UnexpectedValueException::class);
        $mManager = new Menus_manager();

        $mManager->add_menus_items_from_structured_array(
            array(
                "configMenu"    => array(
                    "name"                      => "Configuration",
                    "default_level_needed"      => "user",
                    "groups_allowed"            => array( 1,2 ),
                    "CRUDclass"                 => "testMenusClassUnconsistend",
                    "uriPattern"                => "'^/config(/.*)?$'",
                    "link"                      => "/config"
                )
            )
        );
    }

    public function testAclAllowedMenu():void{

        //guest
        $_SERVER["REQUEST_URI"] = "/";
        $guestuser = new User();
        $currentMenu = new MenuItem ("guestpage", "guestpage", "guest","TestMenusClass1", "'^/$'" , "/", true, array() ); 
        
        $this->assertTrue($currentMenu->is_user_allowed($guestuser));
        //test again with an auth user
        $pdo= new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        UserGroupManager::createTables($pdo);   
        $authUser = UserGroupManager::createUser($pdo, "username", "userlogin", "userpass", array() );
        $authUser->authentificate($pdo, "userlogin", "userpass");
        $this->assertTrue($currentMenu->is_user_allowed($authUser));
        $currentMenu = new MenuItem ("guestpage", "guestpage", "user","TestMenusClass1", "'^/$'" , "/", true, array() ); 
        $this->assertFalse($currentMenu->is_user_allowed($guestuser));
        $this->assertTrue( $currentMenu->is_user_allowed($authUser) );
        $currentMenu = new MenuItem ("guestpage", "guestpage", "user","TestMenusClass1", "'^/$'" , "/", true, array("supergroupe") ); 
        $this->assertFalse($currentMenu->is_user_allowed($guestuser));
        $this->assertFalse( $currentMenu->is_user_allowed($authUser) );
        $authUser->add_group(new group(44,"supergroupe") );
        $this->assertFalse($currentMenu->is_user_allowed($guestuser));
        $this->assertTrue( $currentMenu->is_user_allowed($authUser) );

    }


}