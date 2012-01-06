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

$sql = "SELECT * FROM mount_relay JOIN mounts USING (mount) JOIN relays USING (relay) WHERE mount = $stream GROUP BY (tx / bandwidth) ASC";
$dbres = $db->query($sql);
$streams= array();
if($dbres) {
    while($m = $db->fetch($dbres)) {
        $streams[] = array('name' => 'RfK @ '.$m['hostname'], 'url' => 'http://'.$m['hostname'].':8000'.$m['path']);
    }
}
header('Content-type: audio/x-mpegurl');
header('Content-Disposition: attachment; filename="rfk.m3u"');
echo "#EXTM3U\r\n";
foreach($streams as $stream) {
    echo "#EXTINF:0, ".$stream['name']."\r\n";
    echo $stream['url']."\r\n";
}



?>