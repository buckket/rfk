<?php
require_once('../lib/common-web.inc.php');
$date =time();
$day = date('j', $date) ;
if(isset($_GET['month']) && $_GET['month'] > 0 && $_GET['month'] <13) 
   $month = $_GET['month'];
else{
   $month = date('m', $date);
}
if(isset($_GET['year']) && $_GET['year'] > 0 && $_GET['year'] <9999) 
   $year = $_GET['year'];
else{
   $year = date('Y', $date) ;
}
if(isset($_GET['week']) && $_GET['week'] > 0 && $_GET['week'] <54){
	//weekview
	$template = new BpTemplate('broadcasts-week.html');
	$currweek = strtotime($_GET['year']."W".$_GET['week']);
	$nextweek = strtotime("+1 week",$currweek);
	$prevweek = strtotime("-1 week",$currweek);
	$sql = "SELECT showid,userid,name,showtype,UNIX_TIMESTAMP(begin) as begin, UNIX_TIMESTAMP(IF(end IS NULL,NOW(),end)) as end FROM shows WHERE begin between FROM_UNIXTIME($currweek) AND FROM_UNIXTIME($nextweek) OR end between FROM_UNIXTIME($currweek) AND FROM_UNIXTIME($nextweek) ORDER BY begin asc;";
	$result = $db->query($sql);
	$shows = array();
	//put showdata into an array
	while($show = $db->fetch($result)){
		$temp = array();
		if($show['begin'] >= $currweek){
			$temp['begin'] = floor(date('H',$show[begin])*2)+floor(date('i',$show[begin])/30);
			$temp['beginh'] = date('H',$show[begin]);
			$temp['beginm'] = date('i',$show[begin]);
		}else{
			$temp['begin'] = 0;
			$temp['beginh'] = 0;
			$temp['beginm'] = 0;
		}
		$temp['name'] = $show['name'];
		$temp['type'] = $show['showtype'];
		//if show is between two days
		if(date('d',$show['begin']) != date('d',$show['end'])){
			$temp['end'] = 47;
			$temp['endh'] = 0;
			$temp['endm'] = 0;
			$temp['length'] = $temp['end'] - $temp['begin'];
			//get rid of zerolength-shows
			if($temp['begin'] != $temp['end']){
				$shows[convMonToSun(date('w',$show['begin']))][$temp['begin']] = $temp;
			}
			$temp['begin'] = 0;
			$temp['beginh'] = 0;
			$temp['beginm'] = 0;
		}
		$temp['end'] = floor(date('H',$show[end])*2)+ceil(date('i',$show[end])/30);
		$temp['endh'] = date('H',$show[end]);
		$temp['endm'] = date('i',$show[end]);
		$temp['length'] = $temp['end'] - $temp['begin'];
		//get rid of zerolength-shows
		if($temp['begin'] != $temp['end']){
			if(date('d',$show['begin']) != date('d',$show['end'])){
				$shows[convMonToSun(date('w',$show['end']))][$temp['end']] = $temp;
			}else{
				$shows[convMonToSun(date('w',$show['begin']))][$temp['begin']] = $temp;
			}
		}
	}
	//cleanup and insert free cells
	for($wd = 0; $wd < 7; $wd++){
		for($h = 0; $h < 48; $h++){
			
			if(!is_array($shows[$wd][$h])){
				$shows[$wd][$h] = array('type' => 'FREE', 'begin' => $h,'end' => $h+1,'length' => 1);
			}else{
				for($s = 1;$s < $shows[$wd][$h]['length'];$s++){
					$shows[$wd][$h+$s] = array('type' => 'SKIP', 'begin' => $h+$s,'end' => $h+$s+1,'length' => 1);
				}
				$h += $shows[$wd][$h]['length']-1;
			}
		}
	}
	//reference by time for template
	$calendar = array();
	foreach($shows as $weekdaynum => $day){
		foreach($day as $time => $show){
			$calendar[$time][$weekdaynum] = $show;
		}
	}
	asort($calendar);
	//print_r($calendar);
	$template->assign('calendar',$calendar);
	$template->assign('year',$year);
	$template->assign('week',$_GET['week']);
	$template->assign('nextweek',$nextweek);
	$template->assign('prevweek',$prevweek);
}else{
	//month overview
	//template
	$template = new BpTemplate('broadcasts.html');
	
	$first_day = mktime(0,0,0,$month, 1, $year); 
	$day_of_week = convMonToSun(date('w', $first_day));
	$last_day = mktime(0,0,0,$month, date('t', $first_day), $year);
	$last_day_of_week = convMonToSun(date('w', $last_day));
	$calendar = array();
	//prev month
	if($day_of_week > 0){
		$lastmonth = strtotime("-1 month",$first_day);
		$lastdaylastmonth = date('t', $lastmonth);
		$week = date('W', $first_day);
		for($d = 0;$d <$day_of_week; $d++){
			$day = array();
			$calendar[$week][$d]['day'] = ($lastdaylastmonth-($day_of_week-1))+$d;
			$calendar[$week][$d]['month'] = date('m', $lastmonth);
			$calendar[$week][$d]['year'] = date('Y', $lastmonth);
			$calendar[$week][$d]['thismonth'] = 'false';
			$calendar[$week][$d]['shows'] = getShows($calendar[$week][$d]['day'],$calendar[$week][$d]['month'],$calendar[$week][$d]['year']);
		}
	}
	$curr_month_day = 1;
	$daycount = date('t', $first_day);
	while($curr_month_day <= $daycount){
		$currday = mktime(0,0,0,$month, $curr_month_day, $year);
		$weekday = convMonToSun(date('w', $currday));
		$week = date('W', $currday);
		$calendar[$week][$weekday]['day'] = $curr_month_day;
		$calendar[$week][$weekday]['month'] = $month;
		$calendar[$week][$weekday]['year'] = $year;
		$calendar[$week][$weekday]['thismonth'] = 'true';
		$calendar[$week][$weekday]['shows'] = getShows($calendar[$week][$weekday]['day'],$calendar[$week][$weekday]['month'],$calendar[$week][$weekday]['year']);
		$curr_month_day++;
	}

	if($last_day_of_week < 6){
		$nextmonth = strtotime("+1 month",$first_day);
		$week = date('W', $last_day);
		for($d = 1;$d <= 6-$last_day_of_week; $d++){
			$day = array();
			// -1 ?! keine ahnung
			$calendar[$week][$last_day_of_week+$d]['day'] = $d;
			$calendar[$week][$last_day_of_week+$d]['month'] = date('m', $nextmonth);
			$calendar[$week][$last_day_of_week+$d]['year'] = date('Y', $nextmonth);
			$calendar[$week][$last_day_of_week+$d]['thismonth'] = 'false';
			$calendar[$week][$last_day_of_week+$d]['shows'] = getShows($calendar[$week][$last_day_of_week+$d]['day'],$calendar[$week][$last_day_of_week+$d]['month'],$calendar[$week][$d]['year']);
		}
	}
	$template->assign('calendar',$calendar);
	$template->assign('year',$year);
	$template->assign('monthname',getMonth($month));
}
include('include/listenercount.php');
cleanup($template);
$template->assign('section', "broadcasts");
$template->assign('PAGETITLE', "Sende&uuml;bersicht");
echo $template->render();



function getShows($day,$month,$year){
	global $db;
    $sql = "SELECT count(*) as count FROM shows WHERE DATE(begin) = DATE('".$db->escape($year)."-".$db->escape($month)."-".$db->escape($day)."')";
	$result = $db->fetch($db->query($sql));
	return $result['count'];	
}

function convMonToSun($dow){
	$dow -= 1;
	if($dow < 0){
		$dow = 6;
	}
	return $dow;
}
function getMonth($month){
    switch($month){
        case 1:
            return 'Januar';
        case 2:
            return 'Februar';   
        case 3:
            return 'M&auml;rz';
        case 4:
            return 'April';
        case 5:
            return 'Mai';
        case 6:
            return 'Juni';
        case 7:
            return 'Juli';
        case 8:
            return 'August';
        case 9:
            return 'September';
        case 10:
            return 'Oktober';
        case 11:
            return 'November';
        case 12:
            return 'Dezember';
    }
}
?>
