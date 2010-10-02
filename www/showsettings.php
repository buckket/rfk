<?php
require_once('../lib/common-web.inc.php');
global $lang;
$template = array();
if(!$user->logged_in ){
    require_once 'login.php';
    exit();
}
if(isset($_POST['submit'])) {
    if(isset($_POST['showname'])){
        $sql = "INSERT INTO streamersettings (streamer, `key`, value) VALUES
                (".$user->userid.",'defaultshowname','".$db->escape($_POST['showname'])."')
                ON DUPLICATE KEY UPDATE value = '".$db->escape($_POST['showname'])."'";
        $db->execute($sql);
    }
    if(isset($_POST['showdesc'])){
        $sql = "INSERT INTO streamersettings (streamer, `key`, value) VALUES
                (".$user->userid.",'defaultshowdescription','".$db->escape($_POST['showdesc'])."')
                ON DUPLICATE KEY UPDATE value = '".$db->escape($_POST['showdesc'])."'";
        $db->execute($sql);
    }
    if(isset($_POST['background'])){
        $sql = "INSERT INTO streamersettings (streamer, `key`, value) VALUES
                (".$user->userid.",'background','".$db->escape($_POST['background'])."')
                ON DUPLICATE KEY UPDATE value = '".$db->escape($_POST['background'])."'";
        $db->execute($sql);
    }
    if(isset($_POST['icy'])){
        $sql = "INSERT INTO streamersettings (streamer, `key`, value) VALUES
        (".$user->userid.",'icytags','".$db->escape($_POST['icy'])."')";
        $db->execute($sql);
    }else{
        $sql = "DELETE FROM streamersettings WHERE streamer = ".$user->userid." AND `key` = 'icytags'LIMIT 1;";
        $db->execute($sql);
    }
}
$sql = "SELECT * FROM streamersettings WHERE streamer = ".$user->userid;
$dbres = $db->query($sql);
while($row = $db->fetch($dbres)){
    switch($row['key']){
        case 'icytags';
            $template['icytags'] = true;
            break;
        case 'defaultshowname':
            $template['showname'] = $row['value'];
            break;
        case 'defaultshowdescription':
            $template['showdesc'] = $row['value'];
            break;
        case 'background':
            $template['background'] = $row['value'];
    }
}

$template['PAGETITLE'] = $lang->lang('L_SHOWSETTINGS');
$template['section'] = 'user';
cleanup_h2o($template);
include('include/listenercount.php');
include('include/sidebar.php');
$h2o = new H2o('showsettings.html',$h2osettings);
echo $h2o->render($template);
?>