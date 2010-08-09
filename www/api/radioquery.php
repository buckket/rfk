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
        getDJ($out);
        break;
    case 'show':
        getCurrShow($out);
        getListener($out);
        break;
    case 'nextshows':
        getNextShows($out);
        break;
    case 'tracks':
        getTracks($out);
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
        $out['djid'] = $row['streamer'];
    }
}
function getCurrShow(&$out){
    global $db;
    $sql = 'SELECT `show`, UNIX_TIMESTAMP(begin) as b,UNIX_TIMESTAMP(end) as e,name, description,type
            FROM shows
            JOIN streamer USING (streamer)
            WHERE end IS NULL
               OR NOW() between begin AND end
              AND status = "STREAMING" LIMIT 1;';
    $dbres = $db->query($sql);
    if($dbres) {
        if($row = $db->fetch($dbres)) {
            $out['showbegin'] = $row['b'];
            $out['showend'] = $row['e'];
            $out['showtype'] = $row['type'];
            $out['showname'] = $row['name'];
            $out['showdescription'] = $row['description'];
            $out['showid'] = $row['show'];
        }
    }
}

function getNextShows(&$out){
    global $db;
    if(isset($_GET['c']) && $_GET['c'] > 1){
        $limit = $_GET['c'];
    }else{
        $limit = 1;
    }
    $sql = 'SELECT UNIX_TIMESTAMP(begin) as b,UNIX_TIMESTAMP(end) as e, name, description, type, username
            FROM shows
            JOIN streamer USING (streamer)
            WHERE begin > NOW();
            ORDER BY begin ASC
            LIMIT 0,'.$limit;
    $dbres = $db->query($sql);
    if($dbres) {
        if($row = $db->fetch($dbres)) {
            $out['showbegin'] = $row['b'];
            $out['showend'] = $row['e'];
            $out['showtype'] = $row['type'];
            $out['showname'] = $row['name'];
            $out['showdescription'] = $row['description'];
            $out['showdj'] = $row['username'];
        }
    }
}

function getCurrTrack(&$out) {
    global $db;
    $lasttrack = 0;
    if(isset($_GET['ltid']) && $_GET['ltid'] > 0){
        $lasttrack = $db->escape($_GET['ltid']);
    }
    $sql = "SELECT *
            FROM songhistory
            WHERE end IS NULL
            AND song > ".$lasttrack.";";
    $dbres = $db->query($sql);
    if($dbres) {
        if($row = $db->fetch($dbres)) {
            $out['trackid'] = $row['song'];
            $out['title'] = $row['title'];
            $out['artist'] = $row['artist'];
        }
    }
}
function getListener(&$out){
    global $db;
    $sql = "SELECT name, IF(c IS NULL, 0, c) as c, description
            FROM (SELECT COUNT(*) as c, mount
                  FROM listenerhistory
                  WHERE disconnected IS NULL
                  GROUP BY mount) as c
            RIGHT JOIN mounts USING (mount);";
    $dbres = $db->query($sql);
    if($dbres) {
        while($row = $db->fetch($dbres)) {
            $out['listener'][$row['name']]['description'] = $row['description'];
            $out['listener'][$row['name']]['c'] = $row['c'];
        }
    }
}

function getTracks(&$out){
    global $db;
    if(isset($_GET['c']) && $_GET['c'] > 1){
        $limit = $_GET['c'];
    }else{
        $limit = 1;
    }
    $sql = 'SELECT title, artist
            FROM songhistory
            ORDER BY song DESC
            LIMIT 0,'.$limit.';';
    $dbres = $db->query($sql);
    $tmp = array();
    if($dbres) {
        while($row = $db->fetch($dbres)) {
            $tmp[] = array('title' => $row['title'],
                           'artist' => $row['artist']);
        }
    }
    $out['history'] = $tmp;
}
?>