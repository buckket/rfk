<?php
error_reporting(0);
include('../../../lib/common-web.inc.php');
global $lang;
$data = array();
$_GET += $_POST;
switch($_GET['w']){
    case 'add':
        if($user->logged_in){
            addShow(&$data);
        }else{
            $data['error'][] = array(1,$lang->lang('L_ERR_AUTH_REQUIRED'));
        }
        break;
    case 'delete':
        deleteShow($data);
        break;
    case 'edit':
        if($user->logged_in){
            editShow(&$data);
        }else{
            $data['error'][] = array(1,$lang->lang('L_ERR_AUTH_REQUIRED'));
        }
        break;
    case 'addshowform':
        if($user->logged_in){
            addShowForm(&$data);
        }else{
            $data['error'][] = array(1,$lang->lang('L_ERR_AUTH_REQUIRED'));
        }
        break;
    default:
        if(isset($_GET['id'])){
            echo getShowInfos();
            exit();
        }
}
header('Content-Type: application/json');
echo json_encode($data);
exit();

function getShowInfos(){
    global $db, $bbcode,$includepath;
    require_once $includepath.'/listener.php';
    $ids = explode(',', $_GET['id']);
    $ins = array();
    foreach($ids as $id){
        $ins[] = $db->escape($id);
    }
    $sql = "SELECT name, description, username, UNIX_TIMESTAMP(begin) as begin, UNIX_TIMESTAMP(end) as end
	            FROM shows JOIN streamer USING ( streamer )
	            WHERE `show` IN ('".implode("','",$ins)."')";
    $dbres = $db->query($sql);

    $out = '';
    if($dbres) {
        while($row = $db->fetch($dbres)) {
            if(strlen($out) > 0)
            $out .= '<hr />';
            //list($max,$avg) = getListeners($row['begin'], $row['end']);
            $out .= '<div class="showtt">'.htmlspecialchars($row['name']).' ( '.htmlspecialchars($row['username']).' )<br />
                        '.date('d. m. Y. H:i',$row['begin']).' - '.date('H:i',$row['end']).'<br />';
            if($max) {
                $out .= 'Zuhörer: Max: '.$max.' Ø: '.number_format($avg,2);
            }
            $out .= '<div>'.$bbcode->parse($row['description']).'</div>';
            $out .='</div>';
        }
    }
    return $out;
}
function addShow(&$data){
    global $db,$user,$lang;
    $currweek = (int)$_GET['cw'];
    $sd    = (int)$_POST['start'];
    $length   = (int)$_POST['length'];
    $start = $currweek+(floor($sd/100)*86400+(($sd%100)*1800));
    $end = $start+$length*1800;
    $name     = $_POST['name'];
    $desc     = $_POST['description'];
    if($currweek == 0 || $currweek+$end <= time()){
        $data['error'][] = array('errid'  => 2,
			                         'desc'   => $lang->lang('L_ERR_WRONG_TIME'));
        return;
    }
    if($length > 48){
        $data['error'][] = array('errid'  => 3,
			                         'desc'   => $lang->lang('L_ERR_SHOW_2LONG'));
        return;
    }
    if(!isset($name) || strlen($name) == 0){
        $data['error'][] = array('errid'  => 4,
			                         'desc'   => $lang->lang('L_ERR_NO_NAME'));
        return;
    }
    if(!isset($desc) || strlen($desc) == 0){
        $data['error'][] = array('errid'  => 5,
			                         'desc'   => $lang->lang('L_ERR_NO_DESC'));
        return;
    }
    $tstart = $start+1;
    $tend = $end-1;
    $sql = "SELECT * FROM shows
				WHERE type = 'PLANNED' AND (begin BETWEEN FROM_UNIXTIME($tstart) AND FROM_UNIXTIME($tend)
				OR end BETWEEN FROM_UNIXTIME($tstart) AND FROM_UNIXTIME($tend)
				OR FROM_UNIXTIME($tstart) BETWEEN begin AND end
				OR FROM_UNIXTIME($tend) BETWEEN begin AND end)";
    //echo $sql;
    $result = $db->query($sql);
    $collides = false;
    while($row = $db->fetch($result)){
        $data['error'][] = array('errid'  => 6,
									 'desc'   => "$lang->lang('L_ERR_COLLIDES')".$row['name']);
        $collides = true;
    }
    if($collides){
        return;
    }
    if(!($threadnum = parseThread($_POST['thread']))){
        $threadnum = 'NULL';
    }
    //enter the show
    $sql = "INSERT INTO shows (streamer,name,description,begin,end,type,thread)
		                   VALUES (".$user->userid.",'".$db->escape($name)."','".$db->escape($desc)."',FROM_UNIXTIME($start),FROM_UNIXTIME($end),'PLANNED',$threadnum);";

    if($db->execute($sql)){
        $data['ok'] = $db->insert_id();
    }else{
        $data['error'][] = array('errid'  => 0,
									 'desc'   => $lang->lang('L_ERR_SQL'));
    }
}

function editShow(&$data){
    global $db,$user,$lang;
    $start = strptime($_POST['startd'],'%d.%m.%Y');
    $start = mktime(0,0,0,$start['tm_mon']+1,$start['tm_mday'],$start['tm_year']+1900);
    $start = $start+($_POST['startt']*1800);
    $length   = (int)$_POST['length'];
    $end = $start+$length*1800;
    $name     = $_POST['name'];
    $desc     = $_POST['description'];
    if($start == 0 /*|| $end <= time()*/){
        $data['error'][] = array('errid'  => 2,
                                     'desc'   => $lang->lang('L_ERR_WRONG_TIME'));
        return;
    }
    if($length > 48){
        $data['error'][] = array('errid'  => 3,
                                     'desc'   => $lang->lang('L_ERR_SHOW_2LONG'));
        return;
    }
    if(!isset($name) || strlen($name) == 0){
        $data['error'][] = array('errid'  => 4,
                                     'desc'   => $lang->lang('L_ERR_NO_NAME'));
        return;
    }
    if(!isset($desc) || strlen($desc) == 0){
        $data['error'][] = array('errid'  => 5,
                                     'desc'   => $lang->lang('L_ERR_NO_DESC'));
        return;
    }
    $tstart = $start+1;
    $tend = $end-1;
    $sql = "SELECT * FROM shows
                WHERE type = 'PLANNED' AND (begin BETWEEN FROM_UNIXTIME($tstart) AND FROM_UNIXTIME($tend)
                OR end BETWEEN FROM_UNIXTIME($tstart) AND FROM_UNIXTIME($tend)
                OR FROM_UNIXTIME($tstart) BETWEEN begin AND end
                OR FROM_UNIXTIME($tend) BETWEEN begin AND end)
                AND `show` <> ".$db->escape($_GET['showid']);
    //echo $sql;
    $result = $db->query($sql);
    $collides = false;
    while($row = $db->fetch($result)){
        $data['error'][] = array('errid'  => 6,
                                     'desc'   => "$lang->lang('L_ERR_COLLIDES')".$row['name']);
        $collides = true;
    }
    if($collides){
        return;
    }
    if(!($threadnum = parseThread($_POST['thread']))){
        $threadnum = 'NULL';
    }
    //enter the show
    $sql = "UPDATE shows SET
                name = '".$db->escape($_GET['name'])."',
                description = '".$db->escape($_GET['description'])."',
                begin = FROM_UNIXTIME($start),
                end   = FROM_UNIXTIME($end),
                thread = $threadnum
                WHERE `show` = ".$db->escape($_GET['showid'])." AND streamer = ".$user->userid." LIMIT 1;";

    if($db->execute($sql)){
        $data['ok'] = $db->insert_id();
    }else{
        $data['error'][] = array('errid'  => 0,
                                     'desc'   => $lang->lang('L_ERR_SQL'));
    }
}

function deleteShow(&$data){
    global $db,$user;
    $sql = "DELETE FROM shows WHERE `show` = ".$db->escape($_GET['showid'])." AND streamer = ".$user->userid." LIMIT 1;";
    if($db->execute($sql)){
        $data['ok'] = $db->insert_id();
    }else{
        $data['error'][] = array('errid'  => 0,
                                 'desc'   => $lang->lang('L_ERR_SQL'));
    }
}

function addShowForm(&$data){
    global $db,$user,$lang;
    $start = strptime($_POST['startd'],'%d.%m.%Y');
    $start = mktime(0,0,0,$start['tm_mon']+1,$start['tm_mday'],$start['tm_year']+1900);
    $start = $start+($_POST['startt']*1800);
    $length   = (int)$_POST['length'];
    $end = $start+$length*1800;
    $name     = $_POST['name'];
    $desc     = $_POST['description'];
    if($start == 0 || $end <= time()){
        $data['error'][] = array('errid'  => 2,
                                     'desc'   => $lang->lang('L_ERR_WRONG_TIME'));
        return;
    }
    if($length > 48){
        $data['error'][] = array('errid'  => 3,
                                     'desc'   => $lang->lang('L_ERR_SHOW_2LONG'));
        return;
    }
    if(!isset($name) || strlen($name) == 0){
        $data['error'][] = array('errid'  => 4,
                                     'desc'   => $lang->lang('L_ERR_NO_NAME'));
        return;
    }
    if(!isset($desc) || strlen($desc) == 0){
        $data['error'][] = array('errid'  => 5,
                                     'desc'   => $lang->lang('L_ERR_NO_DESC'));
        return;
    }
    $tstart = $start+1;
    $tend = $end-1;
    $sql = "SELECT * FROM shows
                WHERE type = 'PLANNED' AND (begin BETWEEN FROM_UNIXTIME($tstart) AND FROM_UNIXTIME($tend)
                OR end BETWEEN FROM_UNIXTIME($tstart) AND FROM_UNIXTIME($tend)
                OR FROM_UNIXTIME($tstart) BETWEEN begin AND end
                OR FROM_UNIXTIME($tend) BETWEEN begin AND end)";
    //echo $sql;
    $result = $db->query($sql);
    $collides = false;
    while($row = $db->fetch($result)){
        $data['error'][] = array('errid'  => 6,
                                     'desc'   => "$lang->lang('L_ERR_COLLIDES')".$row['name']);
        $collides = true;
    }
    if($collides){
        return;
    }
    if(!($threadnum = parseThread($_POST['thread']))){
        $threadnum = 'NULL';
    }
    //enter the show
    $sql = "INSERT INTO shows (streamer,name,description,begin,end,thread,type)
                               VALUES (".$user->userid.",'".$db->escape($name)."','".$db->escape($desc)."',FROM_UNIXTIME($start),FROM_UNIXTIME($end),$threadnum,'PLANNED');";


    if($db->execute($sql)){
        $data['ok'] = $db->insert_id();
    }else{
        $data['error'][] = array('errid'  => 0,
                                     'desc'   => $lang->lang('L_ERR_SQL'));
    }
}

function convSunTomon($dow){
    $dow += 1;
    if($dow > 6){
        $dow = 0;
    }
    return $dow;
}

function parseThread($thread) {
    $threadnum = 0;
    if(strlen(trim($thread)) > 0){
        if(preg_match('|krautchan.net/rfk/thread-(\\d+).html|', trim($thread),$matches)){
            $threadnum = (int)$matches[1];
        }
    }else if($thread > 0){
        $threadnum = (int)$thread;
    }
    if(!($threadnum > 0 )){
        return null;
    }
    return $threadnum;
}
?>
