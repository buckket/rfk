<?php
require_once(dirname(__FILE__).'/../lib/common.inc.php');
error_reporting(0); // disable error reporting


$sql = "SELECT relay, hostname, port, query_method, query_user, query_pass FROM relays WHERE query_method <> 'NO_QUERY'";
$dbres = $db->query($sql);
$somefail = false;
if($dbres && $db->num_rows($dbres)) {
    while($server = $db->fetch($dbres)) {
        $traffic = array();
        switch($server['query_method']) {
            case 'REMOTE_ICECAST2_KH':
                if(($traffic = getRemoteStat($server['hostname'], $server['port'], $server['query_user'], $server['query_pass'])) === false) {
                    $somefail = true;
                    continue;
                }
                break;
            case 'LOCAL_VNSTAT':
                $traffic = parseVNStat();
                break;
        }
        adjustUnit(&$traffic['out']);
        if(!updateDatabase($traffic['out']['value'], $server['relay'])){
            $somefail = true;
        }
    }
}

if($somefail) {
    exit(1);
} else {
    exit(0);
}

function getRemoteStat($ip,$port,$username,$password) {
    $tmp = array();

    $page = file_get_contents('http://'.$username.':'.$password.'@'.$ip.':'.$port.'/admin/status.xml');
    if(preg_match('/<outgoing_kbitrate>(\\d+)<\\/outgoing_kbitrate>/', $page, $matches)){
        $tmp['out']['value'] = $matches[1];
        $tmp['out']['unit'] = 'KiBit';
    } else {
        return false;
    }
    return $tmp;
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
        case 'KiBit':
            $traffic['value'] = round(floatval($traffic['value']) / 8);
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
