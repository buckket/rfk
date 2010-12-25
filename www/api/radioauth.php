<?php
include('../../lib/common.inc.php');
$sql = "LOCK TABLES mounts WRITE,relays WRITE,mount_relay WRITE, listenerhistory WRITE;";
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

$sql = "SELECT relay FROM relays WHERE hostname='".$db->escape($_POST['server'])."' LIMIT 1;";
$result = $db->query($sql);
$relayid = -1;
if($db->num_rows($result) > 0) {
    $row = $db->fetch($result);
    $relayid = $row['relay'];
}

if(!($mountid > 0) || !($relayid > 0))
    error_log('rejected listener from relay:'.$_POST['server'].' '.$mountid.' '.$relayid);
    header('icecast-auth-message: something went wrong with getting the relay');
    exit;

if($_POST['action'] === 'mount_add'){
    $sql = "UNLOCK TABLES;";
    $sql = "UPDATE listenerhistory SET disconnected = NOW() WHERE disconnected IS NULL AND mount = $mountid AND relay = $relayid";
    $db->execute($sql);
}else if($_POST['action'] === 'mount_remove'){
    //TODO stub (just do the same as in add)
    $sql = "UPDATE listenerhistory SET disconnected = NOW() WHERE disconnected = NULL AND mount = $mountid AND relay = $relayid";
    $db->execute($sql);
}else if($_POST['action'] === 'listener_add'){
    checkMount($relayid,$mountid);
    $sql = "UNLOCK TABLES;";
    $location = getLocation($_POST['ip']);
    $sql = "INSERT INTO listenerhistory (mount,relay,client,ip,connected,disconnected,useragent, city, country)
            VALUES
            ( $mountid,
              $relayid,
            '".$db->escape($_POST['client'])."',
            INET_ATON('".$db->escape($_POST['ip'])."'),
            NOW(),
            NULL,
            '".$db->escape($_POST['agent'])."',
            '".$db->escape(utf8_encode($location['city']))."',
            '".$db->escape($location['cc'])."');";
    $db->execute($sql);
}else if($_POST['action'] === 'listener_remove'){
    $sql = "UNLOCK TABLES;";
    $sql = "UPDATE listenerhistory SET disconnected = NOW() WHERE mount=$mountid AND relay=$relayid AND disconnected IS NULL AND client = '".$db->escape($_POST['client'])."' LIMIT 1;";
    $db->execute($sql);
}

function checkMount($relayid, $mountid){
    global $db;
    $sql = "SELECT * FROM mount_relay WHERE mount = $mountid AND relay = $relayid";
    $dbres = $db->query($sql);
    if($db->num_rows($dbres)) {
        $info = $db->fetch($dbres);
        $sql = "SELECT count(*) as c FROM listenerhistory WHERE mount = $mount AND relay = $relay AND disconnected IS NULL;";
        $dbres = $db->query($sql);
        $result = $db->fetch($dbres);

        if($result['c'] >= $info['maxlistener']) {
            header('icecast-auth-message: mountpoint is full');
            $sql = "UNLOCK TABLES;";
            $db->execute($sql);
            exit();
        }
    }
}

$sql = "UNLOCK TABLES;";
$db->execute($sql);
//TODO iprangeban
header('icecast-auth-user: 1');
?>
