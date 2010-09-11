<?php
require_once('../lib/common-web.inc.php');
$template = array();
if(isset($_POST['submit'])) {
    if(isset($_POST['application']) && strlen($_POST['application'])>= 5 && strlen($_POST['application']) <= 100){
        $sql = "INSERT INTO apikeys (`key`,application,streamer,flag)
                VALUES ('".create_api_key()."','".$db->escape($_POST['application'])."',".$user->userid.",0);";
        $db->execute($sql);
    }
}

$sql = "SELECT `key`,application FROM apikeys WHERE streamer = ".$user->userid.";";
$dbres = $db->query($sql);
if($dbres){
    while($row = $db->fetch($dbres)){
        $template['apikeys'][] = $row;
    }
}

$template['PAGETITLE'] = 'API-key registration';
$template['section'] = 'user';
cleanup_h2o($template);
include('include/listenercount.php');
include('include/sidebar.php');
$h2o = new H2o('apiregistration.html',$h2osettings);
echo $h2o->render($template);
?>