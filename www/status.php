<?php
require_once('../lib/common-web.inc.php');
$template = array();

$sql = "SELECT mount,name,description,count FROM (SELECT count(*) as count,mount FROM listenerhistory WHERE disconnected IS NULL group by mount) as l RIGHT JOIN mounts USING ( mount)";
$result = $db->query($sql);
$streams = array();
while($row = $db->fetch($result)){
    if(strlen($row['count']) == 0){
        $row['count'] = 0;
    }
    $row['url'] = 'http://'.$_config['icecast_external'].'/'.$row['path'].'.m3u';
    $streams[] = $row;
}
$template['streams'] = $streams;
$sql = "SELECT streamer,username FROM streamer WHERE status = 'STREAMING' LIMIT 1;";
$result = $db->query($sql);
if($db->num_rows($result) > 0){
	$template['streaming'] = true;
	$streamerinfo = $db->fetch($result);
	$template['streamerinfo'] = $streamerinfo;
	$sql = "SELECT `show`,name,begin,end,type FROM shows WHERE streamer = ".$streamerinfo['streamer']. " AND (NOW() BETWEEN begin AND end OR end IS NULL) LIMIT 1;";
	$result = $db->query($sql);
	$show = $db->fetch($result);
	$template['show'] = $show;
}else{
	$template['streaming'] = false;
}
$template['PAGETITLE'] = 'Status';
$template['section'] = 'status';
cleanup_h2o($template);
$h2o = new H2o('status.html',$h2osettings);
include('include/listenercount.php');
include('include/sidebar.php');
echo $h2o->render($template);
?>
