<?php
$basePath = dirname(dirname(dirname(__FILE__)));
require_once $basePath.'/lib/common.inc.php';
$out = array();
switch ($_GET['w']) {
    case 'dj':
        getDJ($out);
        getListener($out);
        break;
    case 'track':
        getCurrTrack($out);
        getListener($out);
        break;
    case 'show':
        break;
    case 'tracks':
        break;
}
echo json_encode($out);

function getDJ(&$out){
    global $db;
    $sql = "SELECT * FROM streamer WHERE status = 'STREAMING' LIMIT 1;";
    $dbres = $db->query($sql);
    if($dbres) {
        $row = $db->fetch($dbres);
        $out['dj'] = $row['username'];
    }
}
function getCurrTrack(&$out) {
    global $db;
    $sql = "SELECT * FROM songhistory WHERE end IS NULL;";
    $dbres = $db->query($sql);
    if($dbres) {
        if($row = $db->fetch($dbres)) {
            $out['title'] = $row['title'];
            $out['artist'] = $row['artist'];
        }
    }
}
function getListener(&$out){
    global $db;
    $sql = "SELECT COUNT(*) as c, name, description
            FROM listenerhistory
            JOIN mounts USING ( mount)
            WHERE disconnected IS NULL
            GROUP BY mount;";
    $dbres = $db->query($sql);
    if($dbres) {
        while($row = $db->fetch($dbres)) {
            $out['listener'][$row['name']]['description'] = $row['description'];
            $out['listener'][$row['name']]['c'] = $row['c'];
        }
    }
}
?>