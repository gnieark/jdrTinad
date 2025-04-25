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

$options = getopt("", ["login:", "password:", "groups::"]);

// Parse command-line arguments
if (empty($options['login']) || empty($options['password'])) {
    fwrite(STDERR, "Usage: php createUser.php --login=<username> --password=<password> [--groups=group1,group2,...] [--display_name=<full name>]\n");
    exit(1);
}


// Retrieve parameters
$login = $options['login'];
$password = $options['password'];
$display_name = isset( $options['display_name']) ? $options['display_name']: $login;
$groups = [];

if (!empty($options['groups'])) {
    $groups = array_map('trim', explode(',', $options['groups']));
    $quotedGroups = array();
    foreach($groups as $group){
        $quotedGroups[] = Database::get_db()->quote($group);
    }
    $groupsObjs = UserGroupManager::get_groups(Database::get_db(), 'name IN (' . implode(",", $quotedGroups) . ')', true);
    $groupsIds = array_keys($groupsObjs);
}else{
    $groupsIds =array();
}





UserGroupManager::createUser(Database::get_db(), $display_name, $login, $password , $groupsIds );




