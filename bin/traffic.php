<?php
require_once(dirname(__FILE__).'/../lib/common.inc.php');
#error_reporting(0); // disable error reporting

if(($mounts = getMaxMounts()) === false) {
    error_log("mounts have to be > 0", 0);
    return 1;
}

$sql = "SELECT relay, hostname, port, status, query_method, query_user, query_pass FROM relays WHERE query_method <> 'NO_QUERY'";
$dbres = $db->query($sql);
$somefail = false;
if($dbres && $db->num_rows($dbres)) {
    while($server = $db->fetch($dbres)) {
        
        // Status
        if(getIcecastStatus($server['hostname'], $server['port'], $server['query_user'], $server['query_pass'], $mounts) === false) {
            // check if server status isn't already set to offline
            if($server['status'] != 'OFFLINE') {
                updateDatabase('status', 'OFFLINE', $server['relay']);
            }
            continue;
        }
        else {
            // server seems to be online
            if($server['status'] != 'ONLINE') {
                updateDatabase('status', 'ONLINE', $server['relay']);
            }
        }
        
        // Traffic
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
        if(!updateDatabase('tx', $traffic['out']['value'], $server['relay'])){
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

function getIcecastStatus($ip,$port,$username,$password,$mounts) {
    $page = file_get_contents('http://'.$username.':'.$password.'@'.$ip.':'.$port.'/admin/status.xml');
    if(preg_match('/<sources>(\\d+)<\\/sources>/', $page, $matches)){
        if($matches[1] != 0) {
            return true;
        }
    }
    return false;
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

function updateDatabase($key, $value, $relay) {
    global $db;
    $sql = "UPDATE relays SET ". $db->escape($key) ." = '" . $db->escape($value) ."' WHERE `relay` = '" . $db->escape($relay) ."';";
    if($db->execute($sql)) {
        if($db->getAffectedRows() > 0) {
            return 0;
        }
    }
}

function getMaxMounts() {
    global $db;
    $sql = "SELECT COUNT(*) as c FROM mounts";
    $dbres = $db->query($sql);
    if($dbres) {
        $row = $db->fetch($dbres);
        return $row['c'];
    }
    else {
        return false;
    }
}
