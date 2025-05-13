<?php

$base_directory = dirname ( get_included_files()[0] );
chdir($base_directory);

//autoload classes
spl_autoload_register(function ($class_name) {
    $classFolders = array(  "../classes/", 
                            "../classes/menus/",
                            "../classes/users/",
                            "../classes/routes/",
                            "../classes/Game/",

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
$options = getopt("", ["login:"]);
// Parse command-line arguments
if (empty($options['login'])) {
    fwrite(STDERR, "Usage: php addGodfatherLinks.php --login=<login>\n");
    exit(1);
}

$login = $options['login'];


// choper l'user_id
$sql = " SELECT id FROM `" . User::get_table_name(). "` WHERE login=:login";
$db = Database::get_db();

$stmt = $db->prepare($sql);

// On suppose que $login contient le login recherché
$stmt->execute([':login' => $login]);

$userId = null;
if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $userId = $row['id'];
}


ProposingLink ::add_links($db, $userId, 3);