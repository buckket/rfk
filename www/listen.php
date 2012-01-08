<?php
$basePath = dirname(dirname(__FILE__));
require_once $basePath.'/lib/common.inc.php';
if(!isset($_GET['stream'])) {
    die('no stream selected');
}

$stream = (int)$_GET['stream'];
if(!($stream > 0)) {
    die('neindu!');
}
if(isset($_GET['type']) && $_GET['type'] === 'plain') {
    $sql = "SELECT hostname, port, path, (tx/bandwidth) as `usage`
              FROM mount_relay
              JOIN mounts USING (mount)
              JOIN relays USING (relay)
             WHERE mount_relay.status = 'ONLINE'
               AND mount = ".$db->escape($stream)."
             ORDER BY (tx/bandwidth) ASC LIMIT 1;";
    $dbres = $db->query($sql);
    if($dbres) {
        if($m = $db->fetch($dbres)) {
            header('location: http://'.$m['hostname'].':'.$m['port'].$m['path']);
        }
    }
    exit;
}

$sql = "SELECT * FROM mounts WHERE mount = ".$db->escape($stream)."";
$dbres = $db->query($sql);
if($dbres) {
    if($m = $db->fetch($dbres)) {
        header('Content-type: audio/x-mpegurl');
        header('Content-Disposition: attachment; filename="rfk.m3u"');
        echo "#EXTM3U\r\n";
        echo "#EXTINF:0, Radio freies Krautchan ".$m['description']."\r\n";
        echo 'http://'.$_config['www_url'].$_config['www_base']."/listen.php?stream=$stream&type=plain\r\n";
    }
}

?>
