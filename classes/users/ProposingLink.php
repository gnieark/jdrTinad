<?php

/*
* Manages referral links for registering new game masters
*/

class ProposingLink {

    const TABLE = "proposinglinks";

    private int $godfather_uid;
    private string $linkUid;

    static public function get_table_name(): string {
        return self::TABLE;
    }
    public function get_godfather_uid(): int{
        return $this->godfather_uid;
    }
    public function get_linkUid():string {
        return $this->linkUid;
    }
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
    public function __construct(int $godfather_uid = null, string $linkUid = null){
        if(!is_null($godfather_uid)){
            $this->godfather_uid = $godfather_uid;
        }
        if(!is_null($linkUid)){
            $this->linkUid = $linkUid;
        }
    }
    public function delete(PDO $db){
        $sql = "DELETE FROM `" . self::TABLE . "` WHERE link_uid = :link_uid LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([':link_uid' => $this-> linkUid ]);

        
    }
    public static function load_link_by_uid(PDO $db, string $link_uid){
        $sql = "SELECT godfather_uid, link_uid FROM `" . self::TABLE . "` WHERE link_uid = :link_uid LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([':link_uid' => $link_uid]);
    
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $link = new self();
            $link->godfather_uid = (int)$row['godfather_uid'];
            $link->linkUid = $row['link_uid'];
            return $link;
        }
    
        return false;
    }


    public static function add_links(PDO $db, int $godfather_id, int $quantity):array{
        $sql = "INSERT INTO `" . self::TABLE . "` (godfather_uid, link_uid) VALUES (:godfather_uid, :link_uid)";
        $stmt = $db->prepare($sql);
    
        $links = array();
        for ($i = 0; $i < $quantity; $i++) {
            $link_uid = uniqid();
            $stmt->execute([
                ':godfather_uid' => $godfather_id,
                ':link_uid' => $link_uid
            ]);
            $links[] = new ProposingLink($godfather_id,$link_uid );
        }
        return $links;
    }
        
}