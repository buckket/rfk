<?php
require_once(dirname(__FILE__).'/../../lib/common-web.inc.php');
require_once(dirname(__FILE__).'/../include/listenercount.php');
global $lang;
if(!$user->logged_in) {
    echo $lang->lang('L_GOAWAY');
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
        echo $lang->lang('L_GOAWAY');
        exit;
    }
}else{
    echo $lang->lang('L_GOAWAY');
    exit;
}
$template['section'] = "admin";
$template['WEBROOT'] = "../";
?>