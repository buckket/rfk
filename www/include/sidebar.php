<?php
$sql = "SELECT count(*) as count FROM streamer";
$result = $db->query($sql);
$usercount = $db->fetch($result);

$template['sb_streamercount'] = $usercount['count'];

$sql = "SELECT song,artist,title,end FROM songhistory WHERE end IS NOT NULL ORDER BY song desc LIMIT 10;";
$result = $db->query($sql);
$songs = array();
if($db->num_rows($result)){

    while($song = $db->fetch($result)){
        $songdata = array();
        $songdata['song'] = $song['artist'] . " - " . $song['title'];
        $songdata['fullsong'] = $songdata['song'];
        $songdata['short'] = 0;
        if (strlen($songdata['song']) > 35) {
            $songdata['song'] = trim(substr($songdata['song'], 0, 32));
            $songdata['short'] = 1;
        }
        $songdata['id'] = $song['song'];
        $songs[] = $songdata;
    }
}

$template['sb_songlist'] = $songs;

$sql = "SELECT streamer,username FROM streamer ORDER BY streamer desc LIMIT 5;";
$result = $db->query($sql);
$streamers = array();
while($streamer = $db->fetch($result)){
    $streamers[] = $streamer;
}
$template['sb_streamer'] = $streamers;

if(isset($shows) || isset($calendar)) {
    $sql = 'SELECT UNIX_TIMESTAMP(begin) as b,UNIX_TIMESTAMP(end) as e, name, description, type, username
            FROM shows
            JOIN streamer USING (streamer)
            WHERE begin > NOW()
            ORDER BY begin ASC
            LIMIT 10';
    $dbres = $db->query($sql);
    if($dbres) {
        while($row = $db->fetch($dbres)) {
            $template['nextshows'][] = array('showname' => $row['name'],
                                             'streamer' => $row['username']);
        }
    }
}
$sql = "SELECT * FROM listenerhistory WHERE disconnected IS NULL;";
$dbres = $db->query($sql);
$disco = array();
while($row = $db->fetch($dbres)) {
    $disco[] = array( "x" => rand(-30,170), "y" => rand(0,48), "country" => checkCB($row['country']));
}

usort($disco, 'sortDisco');

$template['disco'] = $disco;


$sql = "SELECT * FROM streamer WHERE status = 'STREAMING';";
$dbres = $db->query($sql);
if($row = $db->fetch($dbres)) {
    $template['disco_streamer'] = checkCB($row['country']);
    $streamer = $row['streamer'];
}

$sql = "SELECT * FROM streamersettings WHERE streamer = '" . $streamer . "' AND `key` = 'background';";
$dbres = $db->query($sql);
if($row = $db->fetch($dbres)) {
    $template['disco_background'] = $row['value'];
}

function checkCB($country){
    global $includepath;
    if(file_exists($includepath.'/../www/images/cb/'.strtolower($country).'.png'))
    return strtolower($country);
    return 'unknown';
}

function sortDisco($a, $b) {
    if($a['y'] > $b['y']) {
        return -1;
    } else if($a['y'] < $b['y']) {
        return 1;
    }
}
?>
