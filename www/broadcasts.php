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
	$currweek = strtotime($_GET['year']."W".$_GET['week']);
	$nextweek = strtotime("+1 week",$currweek);
	$prevweek = strtotime("-1 week",$currweek);
	$sql = "SELECT `show`,streamer,name,type,UNIX_TIMESTAMP(begin) as begin, UNIX_TIMESTAMP(IF(end IS NULL,NOW(),end)) as end FROM shows WHERE begin between FROM_UNIXTIME($currweek) AND FROM_UNIXTIME($nextweek) OR end between FROM_UNIXTIME($currweek) AND FROM_UNIXTIME($nextweek) ORDER BY begin asc;";
	$result = $db->query($sql);
	$shows = array();
	//put showdata into an array
	while($show = $db->fetch($result)){
		$temp = array();
		if($show['begin'] >= $currweek){
			$temp['begin'] = floor(date('H',$show['begin'])*2)+floor(date('i',$show['begin'])/30);
			$temp['beginh'] = date('H',$show['begin']);
			$temp['beginm'] = date('i',$show['begin']);
		}else{
			$temp['begin'] = 0;
			$temp['beginh'] = 0;
			$temp['beginm'] = 0;
		}
		$temp['id'] = 'show'.$show['showid'];
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
		$temp['end'] = floor(date('H',$show['end'])*2)+ceil(date('i',$show['end'])/30);
		$temp['endh'] = date('H',$show['end']);
		$temp['endm'] = date('i',$show['end']);
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
	$nowb = floor(date('H')*2)+floor(date('i')/30);
	$nowe = floor(date('H')*2)+ceil(date('i')/30);
	$noww = convMonToSun(date('w'));
	for($wd = 0; $wd < 7; $wd++){
		for($h = 0; $h < 48; $h++){
			if(!isset($shows[$wd][$h]) || !is_array($shows[$wd][$h])){
				$shows[$wd][$h] = array('type' => 'FREE', 'begin' => $h,'end' => $h+1,'length' => 1,'name' => ' ', 'id' => 'free'.$h.$wd);

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
			$calendar[$time][$weekdaynum+1] = $show;
		}
	}
	for($time = 0; $time < 48; $time++){
		$calendar[$time][0]['type'] = 'TIME';
		$calendar[$time][0]['name'] = aZ(floor(($time)/2)).':'.aZ((($time)%2)*30).' - '.aZ(floor(($time+1)/2)).':'.aZ((($time+1)%2)*30);
		$calendar[$time][0]['length'] = 1;
	}
	ksortTree($calendar);
	//print_r($calendar);
	$template = array();
	$template['calendar'] = $calendar;
	$template['year'] = $year;
	$template['week'] = $_GET['week'];
	$template['nextweek'] = $nextweek;
	$template['prevweek'] = $prevweek;
	cleanup_h2o($template);
	include('include/listenercount.php');
	$h2o = new H2o('broadcasts-week.html',$h2osettings);
	echo $h2o->render($template);
	exit();
}else{
	//month overview
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
	$template = array();
	include('include/listenercount.php');
    $template['calendar'] = $calendar;
    $template['year'] = $year;
    $template['monthname'] = getMonth($month);
    $template['section'] = "broadcasts";
    $template['PAGETITLE'] = "Sende&uuml;bersicht";
	cleanup_h2o($template);
    $h2o = new H2o('broadcasts.html',$h2osettings);
    echo $h2o->render($template);
}




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
/**
 * Recusive alternative to ksort
 *
 * @author    Kevin van Zonneveld <kevin@vanzonneveld.net>
 * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
 * @version   SVN: Release: $Id: ksortTree.inc.php 223 2009-01-25 13:35:12Z kevin $
 * @link      http://kevin.vanzonneveld.net/
 *
 * @param array $array
 */
function aZ($num){
	if(strlen($num) <= 1){
		$num = '0'.$num;
	}
	return $num;
}
function ksortTree( &$array )
{
    if (!is_array($array)) {
        return false;
    }

    ksort($array);
    foreach ($array as $k=>$v) {
        ksortTree($array[$k]);
    }
    return true;
}

?>
