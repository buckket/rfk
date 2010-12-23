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
$template['streamnum'] = $db->num_rows($result);
$sql = "SELECT streamer,username FROM streamer WHERE status = 'STREAMING' LIMIT 1;";
$result = $db->query($sql);
$streamerinfo = array();
if($db->num_rows($result) > 0){
	$template['streaming'] = true;
	$streamerinfo = $db->fetch($result);
	$template['streamerinfo'] = $streamerinfo;
	$sql = "SELECT `show`,name,description,thread,DATE_FORMAT(begin,'%d.%m.%Y %H:%m') as begin ,end,type FROM shows WHERE streamer = ".$streamerinfo['streamer']. " AND (NOW() BETWEEN begin AND end OR end IS NULL) LIMIT 1;";
	$result = $db->query($sql);
	$show = $db->fetch($result);
	$template['show'] = $show;
}else{
	$template['streaming'] = false;
}
if(isset($streamerinfo['streamer'])
   && $streamerinfo['streamer'] > 0
   && $user->is_logged_in()
   && $user->userid == $streamerinfo['streamer']) {
    $listenerover = array();
    $sql = "SELECT country, city, mounts.description as mount,
                   UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(connected) as time
            FROM listenerhistory JOIN mounts USING ( mount ) WHERE disconnected IS NULL ORDER BY connected ASC;";
    $dbres = $db->query($sql);
    if($dbres) {
        while($row = $db->fetch_assoc($dbres)) {
            $hour = str_pad((floor($row['time']/3600)),2,'0',STR_PAD_LEFT);
            $min = str_pad(floor(($row['time']%3600)/60),2,'0',STR_PAD_LEFT);
            $row['time'] = $hour.':'.$min;
            $row['country'] = checkCB($row['country']);
            $listenerover[] = $row;
        }
    }
    $template['listeneroverview'] = $listenerover;
    $template['userstreaming'] = true;
    $sql = 'SELECT begin, end
              FROM shows
              JOIN streamer USING ( streamer)
             WHERE streamer.status = "STREAMING"
               AND begin <= NOW()
               AND (end >= NOW() OR end IS NULL)
             LIMIT 1';
    $dbres = $db->query($sql);
    // D: versuchen sie dieses nicht zuhause
    if($dbres && $t = $db->fetch_assoc($dbres)){
        if(strlen($t['begin']) > 0 ){
            $sql = 'SELECT count(*) as c FROM
                    (SELECT listenerhistory FROM listenerhistory
                     WHERE connected >= "'.$t['begin'].'"
                       OR disconnected >= "'.$t['begin'].'"';
            $sql .='GROUP BY ip,useragent
                     )as c';
            $dbres = $db->query($sql);
            if($dbres && $row = $db->fetch_assoc($dbres)){
                $template['uniquelistener'] = $row['c'];
            }else{
                $template['uniquelistener'] = 0;
            }
        }else{
            $template['uniquelistener'] = 'FEHLERFEHLERFEHLER';
        }
    }else{
        $template['uniquelistener'] = 'FEHLERFEHLERFEHLER';
    }
}

$template['traffic'] = getTraffic();
$template['PAGETITLE'] = $lang->lang('L_STATUS');
$template['section'] = 'status';
cleanup_h2o($template);
$h2o = new H2o('status.html',$h2osettings);
include('include/listenercount.php');
include('include/sidebar.php');
echo $h2o->render($template);
?>
