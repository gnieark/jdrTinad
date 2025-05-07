<?php
class User {
    const TABLE = "users";
    private $groups = array();
    private string $display_name;
    private string $login;
    private bool $authentified = false;
    private int $id;
    private $boards = array(); // Boards UIDS owned by the user

    static public function get_table_name(): string {
        return self::TABLE;
    }
    public function __construct(){

    }

    public function is_authentified() :bool{
        return $this->authentified;
    }

    public function add_board(string $board_uid):self{
        $this->boards[] = $board_uid;
        return $this;
    }
    public function does_own_board(string $board_uid):bool{
        return (in_array($board_uid,$this->boards));
    }
    public function get_boards():array{
        return $this->boards;
    }


    public function get_id():int{ return $this->id;}
    public function set_id(int $id):User { $this->id = $id; return $this;}

    public function get_display_name():string{ return $this->display_name;}
    public function set_display_name(string $display_name):User{ $this->display_name = $display_name; return $this;}

    public function get_login():string {return $this->login; }
    public function set_login(string $login):User{ $this->login = $login; return $this;}

    public function add_group(Group $group ):User{
        $this->groups[] = $group;
        return $this;
    }

    public function get_groups():array {
        return $this->groups;
    }

    public function set_password(PDO $db, string $clearpassword): User {
        if( !isset($this->id) ){
            throw new Exception('id must be instancied');
            die();
        }
        $sql = "UPDATE `". $this->get_table_name() ."`
                SET `password` = :hashedpassword
                WHERE id= :id;";
        $stmt = $db->prepare($sql);
        $stmt->execute(
            array(
                ":id"               =>  $this->id,
                ":hashedpassword"   =>  password_hash($clearpassword, PASSWORD_DEFAULT)
            )
        );
    }

    private function load_groups(PDO $db){
        $this->groups = array();
        $sql = 
        " SELECT 
            `". Group::get_table_name() ."`.id,
            `". Group::get_table_name() ."`.name
         FROM 
            `". Group::get_table_name() ."`,
            `". UserGroupManager::get_table_name() ."`
         WHERE `". Group::get_table_name() ."`.id = `". UserGroupManager::get_table_name() . "`.group_id
         AND `" . UserGroupManager::get_table_name() . "`.user_id = :userId";

         $sth = $db->prepare($sql);
         $sth->bindParam(':userId', $this->id, PDO::PARAM_INT);   
         $sth->execute();
         while($r = $sth->fetch(PDO::FETCH_ASSOC)){
            $this->add_group( new Group( $r["id"], $r["name"])  );
         }
         return $this;

    }
    public function is_in_group(string $groupname):bool{
        foreach($this->groups as $group){
            if( $group->get_name() == $groupname ){
                return true;
            }

        }
        return false;

    }
    private function load_from_db(PDO $db): User{
        if( !isset($this->id) ){
            throw new Exception('id must be instancied before');
            die();
        }

        $sql = "SELECT 
                        `login`         as user_login,
                        `display_name`  as user_display_name
                FROM ". self::TABLE . " 
                WHERE `id` = :id ;";

        $sth = $db->prepare($sql);
        $sth->bindParam(':id', $this->id, PDO::PARAM_INT);
        $sth->execute();
        if($r = $sth->fetch(PDO::FETCH_ASSOC)){
            $this->set_login( $r["user_login"] )
                 ->set_display_name( $r["user_display_name"])
                 ->load_groups($db);
            return $this;
        }else{
            throw new Exception('id not found on database');
            die();
        }

    } 
    public function authentificate(PDO $db, string $login, string $clearpassword):User{
        $sql = "SELECT id, password FROM `". self::TABLE ."` WHERE login=:login;";
        $sth = $db->prepare($sql);
        $sth->execute( array(":login"  => $login) );
        if( $r = $sth->fetch() ){  

            if(password_verify( $clearpassword, $r["password"]) ){
                
                $this->set_id( $r["id"] );
                $this->load_from_db($db);
                $this->authentified = true;
                return $this;
            }

        }
        $this->authentified = false;
        $this->set_id(-1)
             ->set_login("")
             ->set_display_name("");
        $this->groups = array();
        return $this;
    }


}


