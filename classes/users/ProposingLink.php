<?php

/*
* Manages referral links for registering new game masters
*/

class ProposingLink {

    const TABLE = "proposinglinks";

    private int $godfather_uid;
    private string $linkUid;

    
    public static function create_table(PDO $db){
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    
        if ($driver === 'mysql') {
            $sql = "
                CREATE TABLE IF NOT EXISTS `". self::TABLE . "` (
                    godfather_uid INT NOT NULL,
                    link_uid TEXT NOT NULL,
                    FOREIGN KEY (godfather_uid) REFERENCES `". User::get_table_name()."`(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
        } elseif ($driver === 'sqlite') {
            $sql = "
                CREATE TABLE IF NOT EXISTS `". self::TABLE . "` (
                    godfather_uid INTEGER NOT NULL,
                    link_uid TEXT NOT NULL,
                    FOREIGN KEY (godfather_uid) REFERENCES `". User::get_table_name()."`(id) ON DELETE CASCADE
                );
            ";
        } else {
            throw new \Exception("Unsupported database driver: $driver");
        }
    
        $db->exec($sql);
    }
}