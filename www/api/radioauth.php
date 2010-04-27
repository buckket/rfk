<?php
include('../../lib/common.inc.php');
$sql = "LOCK TABLES mounts WRITE, listenerhistory WRITE;";
$db->execute($sql);
/**
 * GET THE MOUNTID
 */
 if(strlen($_POST['mount']) == 0)
 {
    die('no mountpoint given');
 }
$sql = "SELECT mountid FROM mounts WHERE mount='".$db->escape($_POST['mount'])."' LIMIT 1;";
$result = $db->query($sql);
$mountid = -1;
if($db->num_rows($result) > 0) {
    $row = $db->fetch($result);
    $mountid = $row['mountid'];
}else{
    $sql = "INSERT INTO mounts (mount) VALUES ('".$db->escape($_POST['mount'])."');";
    $db->query($sql);
    $mountid = $db->insert_id();
}

if($_POST['action'] === 'mount_add'){
    $sql = "UNLOCK TABLES;";
    $sql = "UPDATE listenerhistory SET disconnected = NOW() WHERE disconnected IS NULL AND mountid = $mountid";
    $db->execute($sql);
}else if($_POST['action'] === 'mount_remove'){
    //TODO stub (just do the same as in add)
    $sql = "UPDATE listenerhistory SET disconnected = NOW() WHERE disconnected = NULL AND mountid = $mountid";
    $db->execute($sql);
}else if($_POST['action'] === 'listener_add'){
    $sql = "UNLOCK TABLES;";
    $sql = "INSERT INTO listenerhistory (mountid,client,ip,connected,disconnected,useragent)
            VALUES($mountid,'".$db->escape($_POST['client'])."',INET_ATON('".$db->escape($_POST['ip'])."'),NOW(),NULL,'".$db->escape($_POST['agent'])."');";
    $db->execute($sql);
}else if($_POST['action'] === 'listener_remove'){
    $sql = "UNLOCK TABLES;";
    $sql = "UPDATE listenerhistory SET disconnected = NOW() WHERE mountid=$mountid AND disconnected IS NULL AND client = '".$db->escape($_POST['client'])."' LIMIT 1;";
    $db->execute($sql);
}

$sql = "UNLOCK TABLES;";
$db->execute($sql);
//TODO iprangeban
header('icecast-auth-user: 1');
?>
