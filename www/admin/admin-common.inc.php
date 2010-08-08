<?php
require_once(dirname(__FILE__).'/../../lib/common-web.inc.php');
require_once(dirname(__FILE__).'/../include/listenercount.php');
if(!$user->logged_in) {
    echo "WEG";
    exit;
}
$sql = "SELECT value
        FROM streamersettings
        JOIN streamer USING (streamer)
        WHERE `key` = 'admin'
          AND value='true'
          AND streamer = ".$user->userid;
$dbres = $db->query($sql);
if($row = $db->fetch($dbres)){
    if($row['value'] != 'true'){
        echo "WEG";
        exit;
    }
}else{
    echo "WEG!";
    exit;
}
$template['section'] = "admin";
$template['WEBROOT'] = "../";
?>