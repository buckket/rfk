<?php
require_once('../lib/common-web.inc.php');
$template = new BpTemplate('status.html');
include('include/sidebar.php');
include('include/listenercount.php');

$sql = "SELECT mount,description,count FROM (SELECT count(*) as count,mountid FROM listenerhistory WHERE disconnected IS NULL group by mountid) as l RIGHT JOIN mounts USING ( mountid)";
$result = $db->query($sql);
$streams = array();
while($row = $db->fetch($result)){
    if(strlen($row['count']) == 0){
        $row['count'] = 0;
    }
    $streams[] = $row;
}
$template->assign('streams',$streams);
$sql = "SELECT userid,username FROM streamer WHERE status = 'STREAMING' LIMIT 1;";
$result = $db->query($sql);
if($db->num_rows($result) > 0){
	$template->assign('streaming',1);
	$streamerinfo = $db->fetch($result);
	$template->assign('streamerinfo',$streamerinfo);
	$sql = "SELECT showid,name,begin,end,showtype FROM shows WHERE userid = ".$streamerinfo['userid']. " AND (NOW() BETWEEN begin AND end OR end IS NULL) LIMIT 1;";
	$result = $db->query($sql);
	$show = $db->fetch($result);
	$template->assign('show',$show);
}else{
	$template->assign('streaming',1);
}
cleanup($template);
echo $template->render();
?>
