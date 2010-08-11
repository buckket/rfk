<?php
   //error_reporting(1);
   include('../../../lib/common-web.inc.php');
   $data = array();
   $_GET += $_POST;
	switch($_GET['action']){
		case 'addshow':
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
	echo json_encode($data);
	exit();

	function getShowInfos(){
	    global $db, $bbcode;
	    $ids = explode(',', $_GET['id']);
	    $ins = array();
	    foreach($ids as $id){
	        $ins[] = $db->escape($id);
	    }
	    $sql = "SELECT name, description, username, DATE_FORMAT(begin, '%T') as begin, DATE_FORMAT(end, '%T') as end
	            FROM shows JOIN streamer USING ( streamer )
	            WHERE `show` IN ('".implode("','",$ins)."')";
	    $dbres = $db->query($sql);
	    $out;
	    if($dbres) {
	        $out .= '<table>';
            while($row = $db->fetch($dbres)) {
                $out .= "<tr>";
                $out .= '<td colspan=2>'.$row['begin'].'&nbsp;-&nbsp;'.$row['end']."</td>";
                $out .= '</tr><tr>';
                $out .= "<td>Name:</td><td>".$row['name']."</td>";
                $out .= '</tr><tr>';
                $out .= '<td colspan=2>'.$bbcode->parse($row['description']).'<td>';
                $out .= '</tr>';
            }
            $out .= '</table>';
	    }
	    return $out;
	}
	function addShow(&$data){
		global $db,$user;
		$year  = (int)$_GET['year'];
		$week  = (int)$_GET['week'];
		$day   = (int)$_GET['day'];
		$start = (int)$_GET['begin'];
		$length   = (int)$_GET['length'];
		$name     = $_GET['name'];
		$desc     = $_GET['description'];
		if(!($year < 9999 && $year > 0 && $week > 0 && $week <= 53
		     && isset($_GET['day']) && $day >= 0&& $day < 7 && start >= 0 && isset($_GET['begin']) && isset($_GET['length']) )){
			$data['error'][] = array('errid'  => 2,
			                         'desc'   => 'Wrong time');
			return;
		}
		if($length > 48){
			$data['error'][] = array('errid'  => 3,
			                         'desc'   => 'Show to long');
			return;
		}
		if(!isset($name) || strlen($name) == 0){
			$data['error'][] = array('errid'  => 4,
			                         'desc'   => 'No Name Set');
			return;
		}
		if(!isset($desc) || strlen($desc) == 0){
			$data['error'][] = array('errid'  => 5,
			                         'desc'   => 'No Description Set');
			return;
		}
		$begin = strtotime((floor($start/2)).":".(($start%2)*30).":01 {$year}W$week + $day day");
		$end = strtotime("+ ".($length*30)."minute -1 second",$begin);
		/**
		echo date('r',$begin);
		echo "<br />";
		echo date('r',$end);
		echo "<br />";
		print_r($begin);
		print_r($end);
		**/
		$sql = "SELECT * FROM shows
				WHERE begin BETWEEN FROM_UNIXTIME($begin) AND FROM_UNIXTIME($end)
				OR end BETWEEN FROM_UNIXTIME($begin) AND FROM_UNIXTIME($end)
				OR FROM_UNIXTIME($begin) BETWEEN begin AND end
				OR FROM_UNIXTIME($end) BETWEEN begin AND end";
		//echo $sql;
		$result = $db->query($sql);
		$collides = false;
		while($row = $db->fetch($result)){
			$data['error'][] = array('errid'  => 6,
									 'desc'   => 'Collides',
									 'showid' =>$row['showid'],
									 'name'   =>$row['name']);
			$collides = true;
		}
		if($collides){
			return;
		}
		//enter the show
		$sql = "INSERT INTO shows (userid,entered,name,description,begin,end,showtype)
		                   VALUES (".$user->userid.",NOW(),'".$db->escape($name)."','".$db->escape($desc)."',FROM_UNIXTIME($begin),FROM_UNIXTIME($end),'PLANNED');";
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