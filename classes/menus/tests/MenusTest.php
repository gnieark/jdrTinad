<?php

use PHPUnit\Framework\TestCase;

class MenusTest extends TestCase {

    protected PDO $pdo;


    protected function setUp(): void {
        require_once(   dirname(__FILE__) .  "/TestMenusClass.php" );
    }

    public function testLoadMenusDefinitions():void{
        $_SERVER["REQUEST_URI"] = "/kdsjhrgtkrgy";
        $mManager = new Menus_manager();
        $mManager->add_menus_items_from_json_file( dirname(__FILE__) . "/menustests.json");
        $currentMenu = $mManager->get_current_menu();
        //should be 404 menu
        $this->assertEquals("404", $currentMenu->get_name() );
   

    }

}