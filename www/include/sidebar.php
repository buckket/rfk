<?php
$sql = "SELECT count(*) as count FROM streamer";
$result = $db->query($sql);
$usercount = $db->fetch($result);

$template['sb_streamercount'] = $usercount['count'];

$sql = "SELECT song,artist,title FROM songhistory ORDER BY song desc LIMIT 10;";
$result = $db->query($sql);
$songs = array();
if($db->num_rows($result)){
    
    while($song = $db->fetch($result)){
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
?>
