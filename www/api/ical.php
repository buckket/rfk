<?
$basePath = dirname(dirname(dirname(__FILE__)));
require_once $basePath.'/lib/common.inc.php';
require_once $basePath.'/lib/api.inc.php';
require_once $basePath.'/lib/ical/iCalcreator.class.php';

$v = new vcalendar();
$v->setConfig('unique_id', 'radio.krautchan.net');

$v->setProperty('x-wr-calname', 'RfK');
$v->setProperty('x-wr-caldesc', 'Radio freies Krautchan');
$v->setProperty('x-wr-timezone', 'Europe/Berlin');

$v->setProperty('method', 'PUBLISH' );

$out = array();
$curr = array();

getCurrShow(&$curr);
getNextShows(&$out,500);

if($curr['showtype'] == 'PLANNED') {
    $out['shows'][] = $curr;
}

foreach($out['shows'] as $show) {
    $vevent = new vevent();
    $vevent->setProperty('DTSTART', date('Ymd\THis',$show['showbegin']));
    $vevent->setProperty('DTEND', date('Ymd\THis',$show['showend']));
    $vevent->setProperty('SUMMARY', sprintf('%s mit %s',$show['showname'], $show['showdj']));
    $vevent->setProperty('DESCRIPTION', $show['showdescription']);
    
    $v->setComponent($vevent);
}

$v->returnCalendar();

?>
