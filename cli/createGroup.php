<?php

$base_directory = dirname ( get_included_files()[0] );
chdir($base_directory);

//autoload classes
spl_autoload_register(function ($class_name) {
    $classFolders = array(  "../classes/", 
                            "../classes/menus/",
                            "../classes/users/",
                            "../classes/routes/",

                        );
    foreach($classFolders as $folder)
    {
        if(file_exists( $folder . $class_name . '.php')){
            include $folder. $class_name . '.php';
            return;
        }
    }
});

// Ensure the script is run from the command line
if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "Ce script doit être exécuté en ligne de commande.\n");
    exit(1);
}

$options = getopt("", ["name:"]);

// Parse command-line arguments
if (empty($options['name'])) {
    fwrite(STDERR, "Usage: php createGroup.php --name=<groupname>\n");
    exit(1);
}

$name = $options['name'];


UserGroupManager::createGroup(Database::get_db(), $name );
