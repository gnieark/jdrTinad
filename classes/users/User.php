<?php
class User {
    const TABLE = "users";
    private $groups = array();
    private string $display_name;
    private string $login;
    private bool $is_connected = false;
    private int $id;

    static public function get_table_name(): string {
        return self::TABLE;
    }
    public function __construct(){

    }
    public function get_id():int{ return $this->id;}
    public function set_id(int $id):User { $this->id = $id; return $this;}

    public function get_display_name():string{ return $this->display_name;}
    public function set_display_name(string $display_name):User{ $this->display_name = $display_name; return $this;}

    public function get_login():string {return $this->login; }
    public function set_login(string $login):User{ $this->login = $login; return $this;}

    public function add_group(Group $group ):User{
        $this->groups[] = $group;
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
        $db->prepare($sql);
        $db->execute(
            array(
                ":id"               =>  $this->id,
                ":hashedpassword"   =>  password_hash($clearpassword, PASSWORD_DEFAULT)
            )
        );
    }


}


