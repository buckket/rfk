#!/usr/bin/php
<?php
set_time_limit(0);
require_once '../lib/common.inc.php';
require_once '../lib/common-functions.inc.php';
require_once $includepath.'/RRD.php';
$rrddir = $radioroot.'/var/lib/rrd/';
$rrdds = array (new RRDDS('listener', RRDDS::gauge, 120, 0, 'U'));
$rrdrras = array(
new RRDRRA(RRDRRA::last, '0.5', 1, 5040), // 2 minute
new RRDRRA(RRDRRA::avg, '0.5', 15, 2351), // 1/2 hour average
new RRDRRA(RRDRRA::max, '0.5', 15, 2351), // 1/2 hour maximum
new RRDRRA(RRDRRA::min, '0.5', 15, 2351), // 1/2 hour minimum
new RRDRRA(RRDRRA::avg, '0.5', 720, 365), // daily average
new RRDRRA(RRDRRA::max, '0.5', 720, 365), // daily maximum
new RRDRRA(RRDRRA::min, '0.5', 720, 365), // daily minimum
new RRDRRA(RRDRRA::avg, '0.5', 5040, 365), // weekly average
new RRDRRA(RRDRRA::max, '0.5', 5040, 365), // weekly maximum
new RRDRRA(RRDRRA::min, '0.5', 5040, 365) // weekly minimum
);
$mountid = 1;
$starttime = time();
//set this to true for initial import
$init = false;

if($init) {
    $sqls = "SELECT MIN(UNIX_TIMESTAMP(connected)) as t
            FROM listenerhistory
            GROUP BY connected;";
    $dbres = $db->query($sqls);
    $starttime = 0;
    if($row = $db->fetch($dbres)) {
        $starttime = $row['t'];
    }else {
        exit(1);
    }
    $out = array();

    for($i = $starttime; $i < time(); $i += 60) {
        $sql = 'SELECT mount, IF(c IS NULL, 0, c) as c
                FROM (SELECT COUNT(*) as c, mount
                        FROM listenerhistory
                       WHERE connected <= FROM_UNIXTIME('.$i.')
                         AND IF(disconnected IS NULL, NOW(), disconnected) > FROM_UNIXTIME('.$i.')
                       GROUP BY mount) as c
                RIGHT JOIN mounts USING (mount);';
        $res2 = $db->query($sql);
        //echo $sql;
        while($t = $db->fetch($res2)) {
            $rrd = new RRD($rrddir,$t['mount']);
            if($rrd->create($rrdds, $rrdrras,$starttime)) {
                echo "created RRD {$t['mount']}.rrd\n";
            }
            $rrd->update(array('listener'), array('listener' => $t['c']),$i);
            echo 'updated '.$t['mount'].':'.$i.' => '.$t['c']."\n";
        }
        $db->free($res2);
    }
}

//update

$sql = "SELECT mount, IF(c IS NULL, 0, c) as c
        FROM (SELECT COUNT(*) as c, mount
              FROM listenerhistory
              WHERE disconnected IS NULL
              GROUP BY mount) as c
        RIGHT JOIN mounts USING (mount);";
$dbres = $db->query($sql);
if($dbres) {
    while($row = $db->fetch($dbres)) {
        $rrd = new RRD($rrddir,$row['mount']);
        if($rrd->create($rrdds, $rrdrras)) {
            echo "created RRD $mount.rrd\n";
        }
        $rrd->update(array('listener'), array('listener' => $row['c']));
    }
}

//updateing irc
$rrddsirc = array (new RRDDS('users', RRDDS::gauge, 120, 0, 'U'));
$rrd = new RRD($rrddir,irc);
if($rrd->create($rrddsirc, $rrdrras)) {
    echo "created RRD irc.rrd\n";
}
$rrd->update(array('users'), array('users' => getIRCCount()));

?>
