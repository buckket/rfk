<?php
   //error_reporting(1);
   include('../../../lib/common-web.inc.php');
   $data = array();
   $_GET += $_POST;
	switch($_GET['w']){
		case 'add':
			if($user->logged_in){
				addShow(&$data);
			}else{
				$data['error'][] = array(1,'Auth required');
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
                list($max,$avg) = getListeners($row['begin'], $row['end']);
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
		global $db,$user;
		$currweek = (int)$_GET['cw'];
		$sd    = (int)$_POST['start'];
		$length   = (int)$_POST['length'];
		$start = $currweek+(floor($sd/100)*86400+(($sd%100)*1800));
		$end = $start+$length*1800;
		$name     = $_POST['name'];
		$desc     = $_POST['description'];
		if($currweek == 0 || $currweek+$end <= time()){
			$data['error'][] = array('errid'  => 2,
			                         'desc'   => 'fehlerhafte zeit');
			return;
		}
		if($length > 48){
			$data['error'][] = array('errid'  => 3,
			                         'desc'   => 'Sendung ist zu lang');
			return;
		}
		if(!isset($name) || strlen($name) == 0){
			$data['error'][] = array('errid'  => 4,
			                         'desc'   => 'kein Name angegeben');
			return;
		}
		if(!isset($desc) || strlen($desc) == 0){
			$data['error'][] = array('errid'  => 5,
			                         'desc'   => 'keine Beschreibung angegeben');
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
									 'desc'   => 'Die Seundung kollidiert mit '.$row['name']);
			$collides = true;
		}
		if($collides){
			return;
		}
		//enter the show
		$sql = "INSERT INTO shows (streamer,name,description,begin,end,type)
		                   VALUES (".$user->userid.",'".$db->escape($name)."','".$db->escape($desc)."',FROM_UNIXTIME($start),FROM_UNIXTIME($end),'PLANNED');";

		if($db->execute($sql)){
			$data['ok'] = $db->insert_id();
		}else{
			$data['error'][] = array('errid'  => 0,
									 'desc'   => 'SQLERROR');
		}

	}

function convSunTomon($dow){
	$dow += 1;
	if($dow > 6){
		$dow = 0;
	}
	return $dow;
}
?>