<?php
$sql = "SELECT count(*) as count FROM streamer";
$result = $db->query($sql);
$usercount = $db->fetch($result);
$template->assign('sb_streamercount',$usercount['count']);

$sql = "SELECT artist,title FROM songhistory ORDER BY songid desc LIMIT 10;";
$result = $db->query($sql);
$songs = array();
if($db->num_rows($result)){
    
    while($song = $db->fetch($result)){
        if (strlen($song['artist']) >= 15) {
		$song['artist'] = substr($song['artist'], 0, 5);
		$song['artist'] .= '<a href="/history.php" title="Mehr!">...</a>';
	}
	if (strlen($song['title']) >= 15) {
		$song['title'] = substr($song['title'], 0, 5);
		$song['title'] .= '<a href="/history.php" title="Mehr!">...</a>';
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
