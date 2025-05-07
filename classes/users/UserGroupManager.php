
<?php
class UserGroupManager {
    const USERSGROUPSRELTABLE = 'users_groups_rel';
    static public function get_table_name():string{
        return self::USERSGROUPSRELTABLE;
    }
    static public function get_users_boards_rel_table(){
        return User::get_table_name(). "_boards_rel";
    }
    static public function createTables(PDO $db) {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        $userTable = User::get_table_name();
        $groupTable = Group::get_table_name();
        $relTable = self::USERSGROUPSRELTABLE;
    
        if ($driver === 'sqlite') {
            $sql = "
            CREATE TABLE IF NOT EXISTS $userTable (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                login TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                display_name TEXT NOT NULL
            );

            CREATE TABLE IF NOT EXISTS $groupTable (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT UNIQUE NOT NULL
            );

            CREATE TABLE IF NOT EXISTS $relTable (
                user_id INTEGER NOT NULL,
                group_id INTEGER NOT NULL,
                PRIMARY KEY (user_id, group_id),
                FOREIGN KEY (user_id) REFERENCES $userTable(id) ON DELETE CASCADE,
                FOREIGN KEY (group_id) REFERENCES $groupTable(id) ON DELETE CASCADE
            );
            CREATE TABLE IF NOT EXISTS `" . self::get_users_boards_rel_table() ."` (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                board_uid TEXT NOT NULL,
                FOREIGN KEY (user_id) REFERENCES $userTable(id) ON DELETE CASCADE
            );
            ";
        } elseif ($driver === 'mysql') {
            $sql = "
            CREATE TABLE IF NOT EXISTS $userTable (
                id INT AUTO_INCREMENT PRIMARY KEY,
                login VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                display_name VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS $groupTable (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) UNIQUE NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS $relTable (
                user_id INT NOT NULL,
                group_id INT NOT NULL,
                PRIMARY KEY (user_id, group_id),
                FOREIGN KEY (user_id) REFERENCES $userTable(id) ON DELETE CASCADE,
                FOREIGN KEY (group_id) REFERENCES $groupTable(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `" . self::get_users_boards_rel_table() ."` (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                board_uid TEXT NOT NULL,
                FOREIGN KEY (user_id) REFERENCES $userTable(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
        } else {
            throw new Exception("Unsupported database driver: $driver");
            die();
        }

        $db->exec($sql);
    }

    static public function createUser (PDO $db, string $display_name, string $login, string $clearpassword, array $groupsIds = []):User
    {
        //clean groupsIds
        
        foreach($groupsIds as $groupId){
            if(!is_int($groupId)){
                throw new Exception("groupsIds param must contains only ints");
            }
        }
        
        $sql = "INSERT INTO `" . User::get_table_name() . "` (login, password,display_name) VALUES
        (
            :login,
            :password,
            :displayname
        );";
        $st = $db->prepare($sql);
        $st->execute(
            array(
                ":login"            => $login,
                ":password"         => password_hash($clearpassword,PASSWORD_DEFAULT),
                ":displayname"      => $display_name
            )
        );
        $user = new User();
        $user->set_id( $db->lastInsertId() )
             ->set_login( $login )
             ->set_display_name( $display_name );

        foreach($groupsIds as $groupId){
            $user = self::addUserToGroup( $db, $user, self::get_group_by_id( $db, $groupId) );
        }
        return $user;

    }
    static public function addUserToGroup(PDO $db, User $user, Group $group):User{
        $user->add_group($group);
        $sqlRel = "INSERT INTO `" . self::USERSGROUPSRELTABLE . "` (user_id,group_id)
        VALUES(:userid, :groupid)";
        $sth = $db->prepare($sqlRel);
        $sth->execute(
            array(
                ":userid"   => $user->get_id(),
                ":groupid"  => $group->get_id()
            )
        );
        return $user;
    }



    static public function createGroup(PDO $db, string $name): Group{
        $sql = "INSERT INTO `" . Group::get_table_name() . "` (name) VALUES (:name);";
        $sth = $db->prepare($sql);
        $sth->execute(
            array(
                ":name" => $name
            )

        );
        $group = new Group();
        $group->set_id(  $db->lastInsertId()  )->set_name( $name );
        return $group;
    }

    static public function get_groups(PDO $db, string $customCond = "", bool $keysAreIds = false): array {
        $sql = "SELECT id,name FROM  `" . Group::get_table_name() . "`";
        if(!empty($customCond)){
            $sql .= " WHERE " .  $customCond;
        }
        $sql .= ";";
        //echo $sql."\n";
        $sth = $db->prepare($sql);

        $sth->execute();
        $arr = array();
        while( $r = $sth->fetch(PDO::FETCH_ASSOC) ){
            if( $keysAreIds ){
                $arr[ $r["id"] ] = new group( $r["id"], $r["name"] );
            }else{
                $arr[] = new group( $r["id"], $r["name"] );
            }
        }
        return $arr;
    }
    static public function get_group_by_id(PDO $db, int $id ):?Group{
        $customcond = " id = " . $id;
        $groups = self::get_groups($db, $customcond);
        if(isset($groups[0])){
            return $groups[0];
        }
        return false;
    }

    static public function get_users(PDO $db, string $customCond = "", bool $associativebyId = false):array{
        $users = array();
        $sql = "SELECT 
                    `users`.`id` as user_id,
                    `users`.`login`  as user_login,
                    `users`.`display_name`  as user_display_name,
                    `" . Group::get_table_name() . "`.`id` as group_id,
                    `" . Group::get_table_name() . "`.`name` as group_name 
                FROM `" . User::get_table_name() . "` as users
                LEFT JOIN `" . self::get_table_name() . "` as reltable ON reltable.user_id = users.id
                LEFT JOIN `" . Group::get_table_name() . "` ON `" . Group::get_table_name() . "`.id = reltable.group_id";
        
        if(!empty($customCond)){
            $sql .= " WHERE " . $customCond;
        }
        $sql .=";";
        $sth = $db->prepare($sql);
        $sth->execute();
    
        while($r = $sth->fetch(PDO::FETCH_ASSOC) ){
           if(!isset( $users[ $r["user_id"]  ] ) ){
            $users[ $r["user_id"] ] = new user();
            $users[ $r["user_id"] ] -> set_id( $r["user_id"] )
                                    -> set_login( $r["user_login"] )
                                    -> set_display_name( $r["user_display_name"] );

           }
           //group
           if( isset( $r["group_id"] )){
                $users[ $r["user_id"] ] -> add_group( new Group($r["group_id"], $r["group_name"]) ); 
           }
        }
        if( $associativebyId ){
            return $users;
        }
        return array_values($users);
    
    }

}