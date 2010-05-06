<?php
require_once('../lib/common-web.inc.php');
$template = array();
if(isset($_GET['u']) && $_GET['u'] > 0){
	$userinfo = array();
	$sql = "SELECT username,userid FROM streamer WHERE userid = ".$db->escape($_GET['u'])." LIMIT 1;";
	$result = $db->query($sql);
	if($row = $db->fetch($result)){
		$userinfo['username'] = $row['username'];
		$sql = "SELECT SUM(TIMESTAMPDIFF(SECOND,begin,end)) as length, count(*) as showcount from shows WHERE userid = ".$row['userid'];
		$result = $db->query($sql);
		if($row = $db->fetch($result)){
			$time = calctime($row['length']);
			$userinfo['streamtime'] = ($time['days'] > 0?$time['days'].' Tage, ':'').($time['hours'] > 0?$time['hours'].' Stunden, ':'').($time['minutes'] > 0?$time['minutes'].' Minuten':'');
			$userinfo['showcount'] = $row['showcount'];
		}else{
			$userinfo['streamtime'] = 0;
			$userinfo['showcount'] = 0;
		}
	}
	$template['user'] = $userinfo;
}
if($user->logged_in && (!isset($_GET['u']) || $_GET['u'] == $user->userid)){
	$template['user_self'] == true;
}else{
	$template['user_self'] == false;
}
$template['PAGETITLE'] = 'User '.($userinfo?' - '.$userinfo['username']:'');
$template['section'] = 'user';
cleanup_h2o($template);
include('include/listenercount.php');
include('include/sidebar.php');
$h2o = new H2o('user.html',$h2osettings);
echo $h2o->render($template);

function calctime($seconds){
	$time = array();
	$time['days'] = floor($seconds / 86400);
	$time['hours'] = floor(($seconds % 86400)/3600);
	$time['minutes'] = floor((($seconds % 86400)%3600)/60);
	$time['seconds'] = (($seconds % 86400)%3600)%60;
	return $time;
}
?>
