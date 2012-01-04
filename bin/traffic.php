<?php
require_once(dirname(__FILE__).'/../lib/common.inc.php');
error_reporting(0); // disable error reporting

// Set this value accordingly (1 = Master)
$relay = 1;

$traffic = parseVNStat();
adjustUnit(&$traffic['out']);
if (updateDatabase($traffic['out']['value'], $relay)) {
    return 0;
}
else {
    return 1;
}


function parseVNStat() {
    $str = file_get_contents('../var/vnstat');
    $tmp = array();
     if (preg_match('/tx.*?([0-9]+)\\.([0-9]+) (.*?)\/s.*/', $str,$matches)) {
         $tmp['out']['value'] = $matches[1].'.'.$matches[2];
         $tmp['out']['unit'] = $matches[3];
     }
     if (preg_match('/rx.*?([0-9]+)\\.([0-9]+) (.*?)\/s.*/', $str,$matches)) {
         $tmp['in']['value'] = $matches[1].'.'.$matches[2];
         $tmp['in']['unit'] = $matches[3];
     }
    return $tmp;
}

function adjustUnit(&$traffic) {
    switch ($traffic['unit']) {
        case 'KiB':
            $traffic['value'] = round(floatval($traffic['value']) * (2^10) / (10^3));
            break;
        case 'MiB':
            $traffic['value'] = round(floatval($traffic['value']) * (2^20) / (10^6));
            break;
    }
}

function updateDatabase($value, $relay) {
    global $db;
    $sql = "UPDATE relays SET tx = '" . $db->escape($value) ."' WHERE `relay` = '" . $db->escape($relay) ."';";
    if($db->execute($sql)) {
        if($db->getAffectedRows() > 0) {
            return 0;
        }
    }
}
