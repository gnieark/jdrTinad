<?php
class CheckDbStructure {
    private $fileVersion = "../db/version.txt";
    private $db;

    public function __Construct(PDO $db ){
        $this->db = $db;
    }

    public function setfileVersion( string $fileVersion ) :CheckDbStructure {
        $this->fileVersion = $fileVersion;
        return $this;
    }

    private function getVersion():int{
        if(!file_exists($this->fileVersion)){
            file_put_contents($this->fileVersion,'0');
        }
        return intval( file_get_contents( $this->fileVersion ) );
    }
    public function setVersion($version){
        file_put_contents($this->fileVersion,$version);
    }
    public function doNeededStructureUpdates(){
        $version = $this->getVersion();

        if( $version < 2 )
        {
            UserGroupManager::createTables($this->db);
            $version = 2;
            $this->setVersion($version);
        }
        if( $version == 2 ){
            $stmt = $this->db->prepare("PRAGMA table_info(" . User::get_table_name(). ")");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $migration_ever_done = false;

            foreach ($columns as $col) {
                if ($col['name'] === "oauth_id") {
                    $migration_ever_done = true;
                }
            }
            if(!$migration_ever_done){

                $sql = "ALTER TABLE `" . User::get_table_name(). "` ADD COLUMN provider TEXT NOT NULL DEFAULT 'local'";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $sql = "ALTER TABLE `" . User::get_table_name(). "` ADD COLUMN oauth_id TEXT;";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                
                $version = 3;
                $this->setVersion($version);
            }


        }
        //idem version suivante, 
    }
}