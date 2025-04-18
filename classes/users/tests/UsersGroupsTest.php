<?php

use PHPUnit\Framework\TestCase;

class UsersGroupsTest extends TestCase {

    protected PDO $pdo;

    protected function setUp(): void {
        $this->pdo = new PDO('sqlite::memory:');
        //$this->pdo= new PDO('sqlite:' . "test.db");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        UserGroupManager::createTables($this->pdo);
    }
    public function testCreateTables():void{
        $tables = [User::get_table_name(), Group::get_table_name(), UserGroupManager::get_table_name()];
        foreach ($tables as $table) {
            $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
            $this->assertNotFalse($stmt->fetch(), "Table '$table' should exist.");
        }
    }
    public function testUsersTableStructure(): void {
        $stmt = $this->pdo->query("PRAGMA table_info(users)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $expected = ['id', 'login', 'password', 'display_name'];
        $actual = array_column($columns, 'name');
        $this->assertEquals($expected, $actual);
    }
    public function testGroupsTableStructure(): void {
        $stmt = $this->pdo->query("PRAGMA table_info(groups)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $expected = ['id', 'name'];
        $actual = array_column($columns, 'name');
        $this->assertEquals($expected, $actual);
    }
    public function testCreateGroups(): void{

        $wanted_groups = ["plop","kjhrgtui","KJGHKJHgtui"];
        foreach($wanted_groups as $wanted_group ){
            UserGroupManager::createGroup($this->pdo, $wanted_group);
        }
        
        $groups = UserGroupManager::get_groups($this->pdo);
        foreach($groups as $group){
            $this->assertContains($group->get_name(),$wanted_groups);
            
        }
    }
    public function testCreateUsers(): void{
        $groups = shuffle(UserGroupManager::get_groups($this->pdo));

        $usersToCreate = array(
            array(
                "display_name"      => "John Snow",
                "login"             => "jsonw",
                "clearPassword"     => "KJGutyiutèi",
                "groupsIds"         => array()
            ),
            array(
                "display_name"      => "Ygritte",
                "login"             => "ygritte",
                "clearPassword"     => "Ksqdfyuuyiutèi",
                "groupsIds"         => array( $groups[0]->get_id(), $groups[1]->get_id())
            )

        );

        foreach($usersToCreate as $userToCreate){
            UserGroupManager::createUser($this->pdo, $userToCreate["display_name"], $userToCreate["login"], $userToCreate["clearPassword"], $usersToCreate["groupsIds"]);
        }


    }


}