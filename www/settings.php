<?php
require_once('../lib/common-web.inc.php');
global $lang;
$template = array();
if(!$user->logged_in ){
    require_once 'login.php';
    exit();
}

function check_password ($pw) {
    global $db,$user;
    $sql="";
    $result="";
    $db->debugquery = false;
    $sql="SELECT username FROM streamer WHERE streamer = ".$user->userid." AND password=SHA1('".$db->escape($pw)."') LIMIT 1";
    $result= $db->query($sql);
    if ( $db->num_rows($result) == 1) {
        $db->debugquery = true;
        return true;
    }else{
        $db->debugquery = true;
        return false;
    }
}

if(isset($_POST['submit'])) {
    $err = false;
    if(isset($_POST['streampassword'])){
        $sql = "UPDATE streamer SET streampassword = '".$db->escape($_POST['streampassword'])."' WHERE streamer = ".$user->userid." LIMIT 1;";
        $db->execute($sql);
    }
    if(isset($_POST['olduserpass']) && isset($_POST['newuserpass']) && isset($_POST['newuserpass2'])){
        if(strlen($_POST['olduserpass']) != 0 && strlen($_POST['newuserpass']) != 0 && strlen($_POST['newuserpass']) != 0){
            if(check_password($_POST['olduserpass'])){
                if($_POST['newuserpass'] != $_POST['newuserpass2']){
                    $_MSG['err'][] = $lang->lang('L_ERR_PASSMISMATCH');
                    $err = true;
                }else{
                    $sql = "UPDATE streamer SET password = SHA('".$db->escape($_POST['newuserpass'])."') WHERE streamer = ".$user->userid." LIMIT 1;";
                    $db->execute($sql);
                    $user->logout();
                    redirect_to_page('login.php',$_SERVER['PHP_SELF']);
                }
            }else{
                $_MSG['err'][] = $lang->lang('L_ERR_PASSMISMATCH');
                $err = true;
            }
        }
    }
}

$sql = "SELECT * FROM streamer WHERE streamer = ".$user->userid." LIMIT 1";
$dbres = $db->query($sql);
if($dbres){
    if($row = $db->fetch($dbres)){
        $template['streampassword'] = $row['streampassword'];
    }
}
$sql = "SELECT * FROM streamersettings WHERE streamer = ".$user->userid.";";
$template['PAGETITLE'] = $lang->lang('L_SETTINGS');
$template['section'] = 'user';
cleanup_h2o($template);
include('include/listenercount.php');
include('include/sidebar.php');
$h2o = new H2o('settings.html',$h2osettings);
echo $h2o->render($template);
?>