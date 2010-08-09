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
    $sql = "SELECT `show`,
                   streamer,
                   name,
                   type,
                   UNIX_TIMESTAMP(begin) as begin,
                   UNIX_TIMESTAMP(IF(end IS NULL,NOW(),end)) as end
            FROM shows
            WHERE begin BETWEEN FROM_UNIXTIME($currweek) AND FROM_UNIXTIME($nextweek)
               OR end BETWEEN FROM_UNIXTIME($currweek) AND FROM_UNIXTIME($nextweek)
            ORDER BY begin ASC;";
    $result = $db->query($sql);
    $shows = array();
    //put showdata into an array
    while($show = $db->fetch($result)){
        $tmp = array();
        $tmp['id']   = $show['show'];
        $tmp['type'] = strtolower($show['type']);
        $tmp['name'] = $show['name'];
        $begin = getdate($show['begin']);
        $end = getdate($show['end']);
        $times = convMonToSun($begin['wday'])*48 +
                 ($begin['hours']*2) +
                 (round($begin['minutes']/30));
        $timee = convMonToSun($end['wday'])*48 +
                 ($end['hours']*2) +
                 (round($end['minutes']/30));
        //$tmp['st'] = $begin;
        //$tmp['et'] = $end;
        //$tmp['s'] = $times;
        //$tmp['e'] = $timee;

        if($show['begin'] < $currweek){
                $i = 0;
        }else{
            $i = $times;
        }
        if($times > $timee) {
            $timee = 336;
        }
        do{
            $shows[$i%48][floor($i/48)]['shows'][$tmp['id']] = $tmp;
            $shows[$i%48][floor($i/48)]['size'] = 1;
            $i++;
        }while($i < $timee && $i < 336);
    }
    for($d = 0; $d < 7; $d++){
        $lt = false;
        $ld = false;
        for($t = 0; $t < 48; $t++){
            if(isset($shows[$t][$d])){
                if($ld !==false && $lt !== false
                   && count(array_diff_key($shows[$lt][$ld]['shows'], $shows[$t][$d]['shows'])) == 0 ){
                    $shows[$lt][$ld]['size']++;
                    unset($shows[$t][$d]);
                }else {
                    $lt = $t;
                    $ld = $d;
                }
            }else{
                $shows[$t][$d]['type'] = 'free';
                $shows[$t][$d]['size'] = 1;
                $lt = false;
                $ld = false;
            }
        }
    }
    $times = array();
    for($d = 0; $d < 7; $d++){
        for($t = 0; $t < 48; $t++){
            if(isset($shows[$t][$d]['shows'])){
                $type = false;
                foreach ($shows[$t][$d]['shows'] as $key => $value){
                    if(isset($value)){
                        if($type != $value['type']){
                            if($type){
                                $type = 'mixed';
                                break;
                            }
                            $type = $value['type'];
                        }
                    }
                }
                if($type){
                    $shows[$t][$d]['type'] = $type;
                }
                if(isset($shows[$t][$d]['shows'])){
                    $shows[$t][$d]['count'] = count($shows[$t][$d]['shows']);
                }
            }
            if($d == 0){
                    $times[$t] = floor($t/2).':'.($t%2==0?'00':'30');
            }
        }
    }
    ksortTree($shows);
    //print_r($shows);
    $template = array();
    $template['calendar'] = $shows;
    $template['timenames'] = $times;
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
