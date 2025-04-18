<?php

//autoload classes
spl_autoload_register(function ($class_name) {
    $classFolders = array(  "../classes/", 
                            "../classes/menus/",
                            "../classes/users/",
                            "../classes/routes/",
                            "../classes/lists/",
                            "../classes/scheduler/"
                        );
    foreach($classFolders as $folder)
    {
        if(file_exists( $folder . $class_name . '.php')){
            include $folder. $class_name . '.php';
            return;
        }
    }
});

//db con
$databasePath = "../db/db.sql";
try {
    if (!is_dir(dirname($databasePath))) {
        mkdir(dirname($databasePath), 0755, true);
    }
    $db = new PDO('sqlite:' . $databasePath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


} catch (PDOException $e) {
    die("Erreur de connexion à la base SQLite : " . $e->getMessage());
}

//create or update base if needed
$checkDbStructure = new CheckDbStructure($db);
$checkDbStructure->doNeededStructureUpdates();




