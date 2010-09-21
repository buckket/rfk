<?php
/**
 *  Common includes/requires for everything
 *
 *  Website-commons doesn't belong here
 *  this is also used by consoledriven scripts
 */

$includepath = dirname(__file__);
$radioroot = dirname(dirname(__file__));
require_once($includepath.'/../etc/config.inc.php');
require_once($includepath.'/dbi.php');
require_once $includepath.'/lang.php';

$defaultTimezone = 'Europe/Berlin';
date_default_timezone_set($defaultTimezone);
$db = new DBI($_config['mysql-host'],$_config['mysql-user'],$_config['mysql-pass'],$_config['mysql-db'],$defaultTimezone);
$lang = new Lang('de');



function getLocation($ip){
    global $includepath,$radioroot;
    $ret = array('cc' => '', 'city' => '');
    if(file_exists($includepath.'/../var/GeoLiteCity.dat')){
        require_once $includepath.'/geoip/geoipcity.inc';
        geoip_load_shared_mem($includepath.'/../var/GeoLiteCity.dat');
        $gi = geoip_open($radioroot.'/var/GeoLiteCity.dat', GEOIP_SHARED_MEMORY);
        $record = GeoIP_record_by_addr($gi, $ip);
        if(is_object($record)) {
            $ret['cc'] = $record->country_code;
            $ret['city'] = $record->city;
            $region = $record->region;
        }
        geoip_close($gi);
    }
    
    #Bayernball spezial ^__^
    if($ret['cc'] == 'DE' && $region == '02') {
        $ret['cc'] = 'BAY';
    }
    
    return $ret;
}
?>