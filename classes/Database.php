<?php
class Database{
    static private PDO $db;
    static private string $databaseFile = "../db/db.sql";

    static public function get_db():PDO{
        if( !isset(static::$db) ){
   
            try {
                if (!is_dir(dirname(static::$databaseFile))) {
                    mkdir(dirname(static::$databaseFile), 0755, true);
                }
                static::$db = new PDO('sqlite:' . static::$databaseFile );
                static::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            
            } catch (PDOException $e) {
                die("Erreur de connexion à la base SQLite : " . $e->getMessage());
            }


        }
        return static::$db;

    }
    static public function set_databaseFile( string $file ):void{
        static::$databaseFile = $file;
    }
}