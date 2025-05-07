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
        //idem version suivante, 
    }
}