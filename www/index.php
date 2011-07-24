<?php
/**
 * index.php
 */
require_once('../lib/common-web.inc.php');
require_once('../lib/classes/statusbar.php');
require_once('../lib/classes/sidebar.php');
require_once('../lib/classes/Menu.php');
$user = new User();
/**
 * loginstuff
 */
if($user->isLoggedIn(true)) {
    if( isLogout() ) {
        $user->logout();
    }
    $template->addData('USER_LOGGED_IN', true);
} else {
    $template->addData('USER_LOGGED_IN', false);
    if ( isLogin() ) {
        $user->login($_POST['username'], $_POST['password']);
    }
}
$user->setLocale();
/**
 * end setting up user
 */

/**
 * load Site
 */
if ($urlParams->getSite()) {
    $site = Site::loadSiteByName($urlParams->getSite());
} else {
    $site = Site::loadSiteByName('news');
}

/**
 * Render if wanted
 */
if(isset($site)) {
    $sidebar = new Sidebar();
    if($site->render() == Site::$RENDER_TEMPLATE) {
        $menu = new Menu();
        $sidebar->pushToTemplate();
        $menu->pushToTemplate();
        $template->printPage();
    }
}else {
    echo "this is not the site you are looking for!";
}

?>
