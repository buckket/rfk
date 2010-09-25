<?php
require_once('../lib/common-web.inc.php');
$template = array();
$template['user'] = false;
if((!isset($_GET['u']) || strlen($_GET['u']) == 0) && $user->logged_in){
    $template['user'] = getUserInfo($user->userid);
} else if (isset($_GET['u'])){
    if($_GET['u'] > 0) {
        $template['user'] = getUserInfo((int)$_GET['u']);
    }else{
        if($u = getIdByName($_GET['u'])) {
            $template['user'] = getUserInfo($u);
        }
    }
}


$template['PAGETITLE'] = 'User '.($userinfo?' - '.$userinfo['username']:'');
$template['section'] = 'user';
cleanup_h2o($template);
include('include/listenercount.php');
include('include/sidebar.php');
$h2o = new H2o('user.html',$h2osettings);
echo $h2o->render($template);

/**
 * Gets the StreamerID by the given Username
 * @param $username
 */
function getIdByName($username) {
    global $db;
    $sql = "SELECT streamer FROM streamer WHERE username = '".$db->escape($_GET['u'])."' LIMIT 1;";
    $dbres = $db->query($sql);
    if($dbres && $db->num_rows($dbres) == 1) {
        $row = $db->fetch($dbres);
        $id = $row['streamer'];
        $db->free($dbres);
        return $id;
    } else {
        return false;
    }
}

/**
 * Gets information about a user
 * @param integer $user
 */
function getUserInfo( $user) {
    global $db,$lang;
    $sql = "SELECT username,streamer FROM streamer WHERE streamer = ".$db->escape($user)." LIMIT 1;";
    $dbres = $db->query($sql);
    if($dbres && $row = $db->fetch($dbres)) {
        $userinfo['username'] = $row['username'];
        $userid = $row['streamer'];
        $sql = "SELECT SUM(TIMESTAMPDIFF(SECOND,begin,end)) as length, count(*) as showcount from shows WHERE streamer = ".$row['streamer'];
        $result = $db->query($sql);
        if($row = $db->fetch($result)){
            $userinfo['streamtime'] = makeTimeSpanString($row['length']);
            $userinfo['showcount'] = $row['showcount'];
        }else{
            $userinfo['streamtime'] = 0;
            $userinfo['showcount'] = 0;
        }
        $sql = "SELECT name, DATE_FORMAT(begin,'%d.%m.%Y') as d, DATE_FORMAT(begin,'%H:%i') as t, UNIX_TIMESTAMP(begin)-UNIX_TIMESTAMP(NOW()) as due FROM shows WHERE begin >= NOW() AND streamer = ".$userid." LIMIT 1;";
        $dbres = $db->query($sql);
        if($dbres && $row = $db->fetch($dbres)) {
            $userinfo['nextshow']['name'] = $row['name'];
            $userinfo['nextshow']['date'] = $row['d'];
            $userinfo['nextshow']['time'] = $row['t'];
            $userinfo['nextshow']['due'] = makeTimeSpanString($row['due']);
        }
        return $userinfo;
    }
    return false;
}

/**
 * returns a string representation of a timespan
 * @param integer $seconds
 */
function makeTimeSpanString($seconds) {
    global $lang;
    $time = calctime($seconds);
    $str = array();
    if($time['days'] > 0 ) {
        $str[] = $time['days'].' '.$lang->lang('D_DAY'.($time['days']==1?'':'S'));
    }
    if($time['hours'] > 0 ) {
        $str[] = $time['hours'].' '.$lang->lang('D_HOUR'.($time['hours']==1?'':'S'));
    }
    if($time['minutes'] > 0 ) {
        $str[] = $time['minutes'].' '.$lang->lang('D_MINUTE'.($time['minutes']==1?'':'S'));
    }
    if($time['seconds'] > 0 ) {
        $str[] = $time['seconds'].' '.$lang->lang('D_SECOND'.($time['seconds']==1?'':'S'));
    }
    return implode(', ', $str);
}

function calctime($seconds){
	$time = array();
	$time['days'] = floor($seconds / 86400);
	$time['hours'] = floor(($seconds % 86400)/3600);
	$time['minutes'] = floor((($seconds % 86400)%3600)/60);
	$time['seconds'] = (($seconds % 86400)%3600)%60;
	return $time;
}
?>
