<?php
class User {
    const TABLE = "users";
    private $groups = array();
    private string $display_name;
    private string $login;
    private bool $authentified = false;
    private int $id;
    private string $oauth_provider = "";
    private string $oauth_id = "";
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
    public function set_oauth_provider(string $provider):self{
        $allowedProviders = array("local");
        $providersdefs = json_decode( file_get_contents("../config/oauth.json") );
        $allowedProviders = array_merge( $allowedProviders , array_keys( $providersdefs ) );

        if(!in_array($provider,$allowedProviders)){
            throw new Exception('unknowed provider ' . $provider);
        }
        $this->oauth_provider = $provider;
        return $this;
    }
    public function get_oauth_provider():string{
        return $this->oauth_provider;
    }
    public function set_oauth_id(string $oauth_id):self{
        $this->oauth_id = $oauth_id;
        return $this;
    }
    public function remove_board(string $board_uid):self{
        $this->boards = array_values(
            array_filter($this->boards, fn($item) => $item !== $board_uid)
        );
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
    public function load_boards(PDO $db):self{
        $this->boards = array();
        $sql = "SELECT board_uid FROM `" . UserGroupManager::get_users_boards_rel_table()."`
                WHERE user_id=:userid;";
        $sth = $db->prepare($sql);
        $sth->bindParam(':userid', $this->id, PDO::PARAM_INT);  
        $sth->execute();
        while($r = $sth->fetch(PDO::FETCH_ASSOC)){
           $this->add_board( $r["board_uid"] );
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
    public function load_from_db(PDO $db): User{
        if( !isset($this->id) ){
            throw new Exception('id must be instancied before');
            die();
        }

        $sql = "SELECT 
                        `login`         as user_login,
                        `display_name`  as user_display_name,
                        `oauth_id`      as oauth_id,
                        `provider`      as oauth_provider,
                FROM ". self::TABLE . " 
                WHERE `id` = :id ;";

        $sth = $db->prepare($sql);
        $sth->bindParam(':id', $this->id, PDO::PARAM_INT);
        $sth->execute();
        if($r = $sth->fetch(PDO::FETCH_ASSOC)){
            $this->set_login( $r["user_login"] )
                 ->set_display_name( $r["user_display_name"])
                 ->set_oauth_id( $r["oauth_id"] )
                 ->set_oauth_provider( $r["oauth_provider"])
                 ->load_groups($db)
                 ->load_boards($db);

            return $this;
        }else{
            throw new Exception('id not found on database');
            die();
        }

    } 
    public function authentificate(PDO $db, string $login, string $clearpassword):self{
        $sql = "SELECT id, password FROM `". self::TABLE ."` WHERE login=:login; AND provider='local'";
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
    public function authentificated_oauth(PDO $db, string $oauth_provider, string $oauth_id):self{
        $sql = "SELECT id FROM `". self::TABLE ."` WHERE oauth_id=:oauthid; AND provider=:oauthprovider;";
        $sth = $db->prepare($sql);
        $sth->bindParam(':oauthid', $oauth_id, PDO::PARAM_STR);
        $sth->bindParam(':oauthprovider', $oauth_provider, PDO::PARAM_STR);
        $sth->execute();
        if( $r = $sth->fetch() ){  
            $this->set_id( $r["id"] );
            $this->load_from_db($db);
            $this->authentified = true;
            return $this;
        }
        $this->authentified = false;
        $this->set_id(-1)
             ->set_login("")
             ->set_display_name("");
        $this->groups = array();
        return $this;
    }


}


