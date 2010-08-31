<?php
require_once('../lib/common-web.inc.php');
$template = array();
if(!$user->logged_in ){
    require_once 'login.php';
    exit();
}
if(isset($_POST['submit'])) {
    if(isset($_POST['streampassword'])){
        $sql = "UPDATE streamer SET streampassword = '".$db->escape($_POST['streampassword'])."' WHERE streamer = ".$user->userid." LIMIT 1;";
        $db->execute($sql);
    }
}

$sql = "SELECT * FROM streamer WHERE streamer = ".$user->userid." LIMIT 1";
$dbres = $db->query($sql);
if($dbres){
    if($row = $db->fetch($dbres)){
        $template['streampassword'] = $row['streampassword'];
    }
}
$sql = "SELECT * FROM streamersettings WHERE streamer = ".$this->userid.";";
$template['PAGETITLE'] = 'Einstellungen';
$template['section'] = 'user';
cleanup_h2o($template);
include('include/listenercount.php');
include('include/sidebar.php');
$h2o = new H2o('settings.html',$h2osettings);
echo $h2o->render($template);
?>