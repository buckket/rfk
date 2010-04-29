<?php
require_once('../lib/common-web.inc.php');
$template = new BpTemplate('broadcasts.html');
include('include/listenercount.php');

$date =time ();
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
		$calendar[$week][$d]['thismonth'] = false;
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
	$calendar[$week][$weekday]['thismonth'] = true;
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
		$calendar[$week][$last_day_of_week+$d]['thismonth'] = false;
		$calendar[$week][$last_day_of_week+$d]['shows'] = getShows($calendar[$week][$last_day_of_week+$d]['day'],$calendar[$week][$last_day_of_week+$d]['month'],$calendar[$week][$d]['year']);
	}
}
$template->assign('calendar',$calendar);
$template->assign('year',$year);
$template->assign('monthname',getMonth($month));
cleanup($template);
$template->assign('section', "broadcasts");
$template->assign('PAGETITLE', "Sende&uuml;bersicht");
echo $template->render();



function getShows($day,$month,$year){
	global $db;
    $sql = "SELECT count(*) as count FROM shows WHERE DATE(begin) = DATE('".$db->escape($year)."'-'".$db->escape($month)."'-'".$db->escape($day)."')";
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
            return 'März';
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
