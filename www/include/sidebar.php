<?php
$sql = "SELECT count(*) as count FROM streamer";
$result = $db->query($sql);
$usercount = $db->fetch($result);
$template->assign('sb_streamercount',$usercount['count']);

$sql = "SELECT songid,artist,title FROM songhistory ORDER BY songid desc LIMIT 10;";
$result = $db->query($sql);
$songs = array();
if($db->num_rows($result)){
    
    while($song = $db->fetch($result)){
	$id = $song['songid'];
	$song = $song['artist'] . " - " . $song['title'];
        if (strlen($song) > 35) {
		$song = trim(substr($song, 0, 32));
		$song .= "<a href=\"/history.php?id={$id}\" title=\"Mehr!\">...</a>";
	}
	$songs[] = $song;
    }
}

$template->assign('sb_songlist',$songs);

$sql = "SELECT userid,username FROM streamer ORDER BY userid desc LIMIT 5;";
$result = $db->query($sql);
$streamers = array();
while($streamer = $db->fetch($result)){
    $streamers[] = $streamer;
}
$template->assign('sb_streamer',$streamers);
?>
