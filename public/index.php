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

date_default_timezone_set( Config::get_option_value("STRUCTURE","time_zone",true) );

//db con
$dbParams = json_decode ( file_get_contents("../config/sql.json"), true );
try {
    $db = new PDO($dbParams["dsn"], $dbParams["user"], $dbParams["password"]);
} catch (PDOException $e) {
    echo 'Connexion échouée : ' . $e->getMessage();
}


//check for sql structure updates
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
    $currentUser->set_db($db);
}else{
    $currentUser = new User($db);
}
if(isset( $_SERVER['HTTP_X_API_KEY'])){
    $currentUser = User_Manager::authentificate_by_token($db,$_SERVER['HTTP_X_API_KEY'] );
}

$_SESSION["user"] = serialize($currentUser);


//load available menus
$mManager = new Menus_manager();

$mManager->add_menus_items_from_json_file( realpath( __DIR__ . '/../') . '/config/menus.json');

//Apply current Menu:
$currentMenu = $mManager->get_current_menu();

switch( $_SERVER['REQUEST_METHOD'] ){
    case "POST":
        $messages = $currentMenu->apply_post($db,$currentUser);
        break;
    case "DELETE":
        $messages = $currentMenu->apply_delete($db,$currentUser);
        break;
    case "PATCH":
        $messages = $currentMenu->apply_patch($db,$currentUser);
        break;
    default:
        $messages = "";
}


if(!$currentMenu->display_on_page())
{
    
    // on n'envoie rien d'autre que le content
    $currentMenu->send_content($db,$currentUser);
    die();
}

//show the page

$tpl = new TplBlock();
$tpl->addVars(
    array(
        "headTitle" => $currentMenu->get_name(),
        'logotext'  => Config::get_option_value("STRUCTURE","logo_text",true),
        "site_title"    => Config::get_option_value("STRUCTURE","site_title",true),
        "site_desc"    => Config::get_option_value("STRUCTURE","site_desc",true),
        "footer_text"   => nl2br(Config::get_option_value("STRUCTURE","footer_text",true)),
        "userSnippet"  => $currentUser->snippetHTML(),
        "headerTitle" => $currentMenu->get_name(),
        "customJS"  => $currentMenu->get_custom_js($db,$currentUser),
        "customCSS" => $currentMenu->get_custom_css($db,$currentUser),
        "content"   => $currentMenu->get_content_html($db,$currentUser),
        "after_body_tag" => $currentMenu->get_custom_after_body_tag($db,$currentUser)
    )
);
//liens en pied de page
$footersLinks = DataList_Footer_Links::GET($db, $currentUser, false, null, null, null,'`' . DataList_Footer_Links::get_table_name() . '`.`order` ASC' );
foreach($footersLinks as $footerLink){
    $tplfooterlinks = new TplBlock("footerlinks");
    $tplfooterlinks->addVars(array(
        "target"    => ($footerLink["external"] == 1) ? "_blank": "_self",
        "url"       => $footerLink["url"],
        "caption"   => htmlentities( $footerLink["caption"] )
    ));
    $tpl->addSubBlock($tplfooterlinks);
}

//menu de navigation
//ajouter les directions

$tree = DataList_Branches::get_Node($db);
foreach( $tree->get_childs() as $branche )
{
    $tplNav = new TplBlock("navdirections");
    $tplNav ->addVars(
        array(
            "url"  =>"/d/" . $branche->get_data()["short_name"],
            "caption"  => htmlentities( $branche->get_data()["short_name"]),
            "current"   => ("/d/" . $branche->get_data()["short_name"] == urldecode($_SERVER["REQUEST_URI"])) ? 'aria-current="page"' : ""
        )
    );
    $tpl->addSubBlock($tplNav);
}


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

//Si l'user est admin, on ajoute le menu pour créer une direction
if( $currentUser->is_admin() ){
    $tplNav = new TplBlock("navmenus");
    $tplNav ->addVars(
        array(
            "url"  => "/d/addBranche/0",
            "caption"  => "Ajouter un service"
        )
    );
    $tpl->addSubBlock($tplNav);
}



echo $tpl->applyTplFile("../templates/main.html");