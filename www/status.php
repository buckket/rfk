<?php
require_once('../lib/common-web.inc.php');
$template = array();
global $lang;
$sql = "SELECT mount,name,description,count,path FROM (SELECT count(*) as count,mount FROM listenerhistory WHERE disconnected IS NULL group by mount) as l RIGHT JOIN mounts USING ( mount )";
$result = $db->query($sql);
$streams = array();
while($row = $db->fetch($result)){
    if(strlen($row['count']) == 0){
        $row['count'] = 0;
    }
    $row['url'] = 'http://'.$_config['icecast_external'].':'.$_config['icecast_port'].$row['path'].'.m3u';
    $streams[] = $row;
}
$template['streams'] = $streams;
$sql = "SELECT streamer,username FROM streamer WHERE status = 'STREAMING' LIMIT 1;";
$result = $db->query($sql);
if($db->num_rows($result) > 0){
	$template['streaming'] = true;
	$streamerinfo = $db->fetch($result);
	$template['streamerinfo'] = $streamerinfo;
	$sql = "SELECT `show`,name,description,begin,end,type FROM shows WHERE streamer = ".$streamerinfo['streamer']. " AND (NOW() BETWEEN begin AND end OR end IS NULL) LIMIT 1;";
	$result = $db->query($sql);
	$show = $db->fetch($result);
	$template['show'] = $show;
}else{
	$template['streaming'] = false;
}

if($user->logged_in == $template['streamerinfo']) {


    $sql = "SELECT useragent, country, city, mount, TIME_FORMAT(UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(connected),'%H:%i') as connected FROM listenerhistory WHERE disconnected IS NULL ORDER BY country;";
    $result = $db->query($sql);
    $listeners = array();
    while($row = $db->fetch($result)){
        $row['mount'] = $mounts[$row['mount']];
        $listeners[] = $row;
    }
    $template['listeners'] = $listeners;
    $template['userIsStreaming'] = true;
}

$template['PAGETITLE'] = $lang->lang('L_STATUS');
$template['section'] = 'status';
cleanup_h2o($template);
$h2o = new H2o('status.html',$h2osettings);
include('include/listenercount.php');
include('include/sidebar.php');
echo $h2o->render($template);
?>
