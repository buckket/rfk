<?php
require_once '../lib/common.inc.php';
require_once $includepath.'/RRD.php';
$rrddir = $radioroot.'/var/lib/rrd/';
$rrd = new RRD($rrddir,1);

$sql = "SELECT * FROM mounts;";
$dbres = $db->query($sql);
$mounts = array();
$defs = array();
$graphs = array();
$cmds = array();
$colors = array('FF9900',
                '9900FF',
                '00FF99',
                '0099FF',
                '99FF00');
$ci = 0;
$graphs[] = new RRDAREA('listenerg', $colors[$ci++],'Gesamt');
while($row = $db->fetch($dbres)) {
    if($ci >= count($colors))
        $ci = 0;
    $mounts[$row['mount']] = $row;
    $defs[] = new RRDDEF('listener'.$row['mount'], $rrddir.$row['mount'].'.rrd','listener', RRDRRA::last);
    $cmds[] = 'listener'.$row['mount'];
    $graphs[] = new RRDLINE(1, 'listener'.$row['mount'], $colors[$ci++],$row['description']);
}
for($i = count($cmds) ; $i > 1 ; $i--) {
    $cmds[] = '+';
}
$cdefs = array();

$cdefs[] = new RRDCDEF('listenerg', $cmds);
$vdefs = array();
header('Content-type: image/png');

$rrd->setHeight(100);
$rrd->setWidth(400);
if(isset($_GET['time'])){
    list($start,$end) = explode(',', $_GET['time']);
    if($start > 0 && $end > 0){
        $rrd->setStart($start);
        $rrd->setEnd($end);
    }
} else {
    $rrd->setStart('now-1day');
}
echo $rrd->createGraph($defs,$graphs,$cdefs);