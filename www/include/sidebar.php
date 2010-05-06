<?php
$sql = "SELECT count(*) as count FROM streamer";
$result = $db->query($sql);
$usercount = $db->fetch($result);
if(is_array($template)){
	$template['sb_streamercount'] = $usercount['count'];
}else{
	$template->assign('sb_streamercount',$usercount['count']);
}
$sql = "SELECT songid,artist,title FROM songhistory ORDER BY songid desc LIMIT 10;";
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
	$songdata['id'] = $song['songid'];
	$songs[] = $songdata;
    }
}
//TODO remove beilpuz code
if(is_array($template)){
	$template['sb_songlist'] = $songs;
}else{
	$template->assign('sb_songlist',$songs);
}
$sql = "SELECT userid,username FROM streamer ORDER BY userid desc LIMIT 5;";
$result = $db->query($sql);
$streamers = array();
while($streamer = $db->fetch($result)){
    $streamers[] = $streamer;
}
if(is_array($template)){
	$template['sb_streamer'] = $streamers;
}else{
	$template->assign('sb_streamer',$streamers);
}
?>
