<?php
/**
 * index.php
 */
require_once('../lib/common-web.inc.php');
require_once('../lib/classes/statusbar.php');
$user = new User();
/**
 * loginstuff
 */
if($user->isLoggedIn(true)) {
    if( isLogout() ) {
        $user->logout();
    }
} else {
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
    if(isset($site)) {
        if($site->render() == Site::$RENDER_TEMPLATE) {
            $template->printPage();
        }
    }else {
        echo "this is not the site you are looking for!";
    }
} else {

    $site = Site::loadSiteByName('news');
    if(isset($site)) {
        if($site->render() == Site::$RENDER_TEMPLATE) {
            $statusbar = new StatusBar();
            $statusbar->pushToTemplate();
            $template->printPage();
        }
    }
}
?>
