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
                'FF9900');
$ci = 0;
while($row = $db->fetch($dbres)) {
    if($ci >= count($colors))
        $ci = 0;
    $mounts[$row['mount']] = $row;
    $defs[] = new RRDDEF('listener'.$row['mount'], $rrddir.$row['mount'].'.rrd','listener', RRDRRA::last);
    $cmds[] = 'listener'.$row['mount'];
    $graphs[] = new RRDLINE(1, 'listener'.$row['mount'], $colors[$ci++],$row['description']);
}
$graphs[] = new RRDLINE(1, 'listenerg', $colors[$ci++],'Gesammt');
for($i = count($cmds) ; $i > 1 ; $i--) {
    $cmds[] = '+';
}
$cdefs = array();

$cdefs[] = new RRDCDEF('listenerg', $cmds);
$vdefs = array();
header('Content-type: image/png');
echo $rrd->createGraph($defs,$graphs,$cdefs);