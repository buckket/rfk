<?php
include('../../lib/common.inc.php');
$sql = "LOCK TABLES mounts WRITE, listenerhistory WRITE;";
$db->execute($sql);
/**
 * GET THE MOUNTID
 */
 if(isset($_POST['mount']) && strlen($_POST['mount']) == 0)
 {
    die('no mountpoint given');
 }
$sql = "SELECT mount FROM mounts WHERE path='".$db->escape($_POST['mount'])."' LIMIT 1;";
$result = $db->query($sql);
$mountid = -1;
if($db->num_rows($result) > 0) {
    $row = $db->fetch($result);
    $mountid = $row['mount'];
}
if(!($mountid > 0))
    header('icecast-auth-user: 0');

if($_POST['action'] === 'mount_add'){
    $sql = "UNLOCK TABLES;";
    $sql = "UPDATE listenerhistory SET disconnected = NOW() WHERE disconnected IS NULL AND mount = $mountid";
    $db->execute($sql);
}else if($_POST['action'] === 'mount_remove'){
    //TODO stub (just do the same as in add)
    $sql = "UPDATE listenerhistory SET disconnected = NOW() WHERE disconnected = NULL AND mount = $mountid";
    $db->execute($sql);
}else if($_POST['action'] === 'listener_add'){
    $sql = "UNLOCK TABLES;";
    $location = getLocation($_POST['ip']);
    $sql = "INSERT INTO listenerhistory (mount,client,ip,connected,disconnected,useragent, city, country)
            VALUES
            ( $mountid,
            '".$db->escape($_POST['client'])."',
            INET_ATON('".$db->escape($_POST['ip'])."'),
            NOW(),
            NULL,
            '".$db->escape($_POST['agent'])."',
            '".$db->escape($location['city'])."',
            '".$db->escape($location['cc'])."');";
    $db->execute($sql);
}else if($_POST['action'] === 'listener_remove'){
    $sql = "UNLOCK TABLES;";
    $sql = "UPDATE listenerhistory SET disconnected = NOW() WHERE mount=$mountid AND disconnected IS NULL AND client = '".$db->escape($_POST['client'])."' LIMIT 1;";
    $db->execute($sql);
}

$sql = "UNLOCK TABLES;";
$db->execute($sql);
//TODO iprangeban
header('icecast-auth-user: 1');

function getLocation($ip){
    global $includepath;
    $ret = array('cc' => '', 'city' => '');
    if(file_exists($includepath.'/../var/GeoLiteCity.dat')){
        require_once $includepath.'/geoip/geoipcity.inc';
        geoip_load_shared_mem($includepath.'/../var/GeoLiteCity.dat');
        $gi = geoip_open($includepath.'/../var/GeoLiteCity.dat', GEOIP_SHARED_MEMORY);
        $record = GeoIP_record_by_addr($gi, $ip);
        $ret['cc'] = $record->countrycode;
        $ret['city'] = $record->city;
    }
    return $ret;
}
?>
