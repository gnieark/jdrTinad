<?php

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


//here we open session
@session_start();

//logout
if($_SERVER["REQUEST_URI"] == "/logout"){
    unset($_SESSION["user"]);
    header('Location: /'); 
    die();
}


if(isset($_SESSION['user'])){
    //session user déjà instanciée précédement
    $currentUser = unserialize($_SESSION["user"]);
 
}else{
    $currentUser = new User();
}


$_SESSION["user"] = serialize($currentUser);

//load available menus
$mManager = new Menus_manager();

$mManager->add_menus_items_from_json_file( realpath( __DIR__ . '/../') . '/config/menus.json');
//Apply current Menu:
$currentMenu = $mManager->get_current_menu();


switch( $_SERVER['REQUEST_METHOD'] ){
    case "POST":
        $messages = $currentMenu->apply_post($currentUser);
        break;
    case "DELETE":
        $messages = $currentMenu->apply_delete($currentUser);
        break;
    case "PATCH":
        $messages = $currentMenu->apply_patch($currentUser);
        break;
    default:
        $messages = "";
}

if(!$currentMenu->display_on_page())
{
    // only send the content
    $currentMenu->send_content($currentUser);
    die();
}

//show the page
$tpl = new TplBlock();
$tpl->addVars(
    array(
        "headTitle" => $currentMenu->get_name(),
        "customJS"  => $currentMenu->get_custom_js($currentUser),
        "customCSS" => $currentMenu->get_custom_css($currentUser),
        "content"   => $currentMenu->get_content_html($currentUser),
        "after_body_tag" => $currentMenu->get_custom_after_body_tag($currentUser)
    )
);

$navMenus = $mManager->get_user_menu_list($currentUser,true);
foreach($navMenus as $navItem){
    $tplNav = new TplBlock("navmenus");
    $tplNav ->addVars(
        array(
            "url"  => $navItem->get_link(),
            "caption"  => htmlentities($navItem->get_name()),
            "current"  => ($navItem == $currentMenu)? 'aria-current="page"' : ''
        )
    );
    $tpl->addSubBlock($tplNav);

}
echo $tpl->applyTplFile("../templates/main.html");