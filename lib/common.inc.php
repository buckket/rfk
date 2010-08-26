<?php
/**
 *  Common includes/requires for everything
 *
 *  Website-commons doesn't belong here
 *  this is also used by consoledriven scripts
 */
date_default_timezone_set('Europe/Berlin');
$includepath = dirname(__file__);
$radioroot = dirname(dirname(__file__));
require_once($includepath.'/../etc/config.inc.php');
require_once($includepath.'/dbi.php');

$db = new DBI($_config['mysql-host'],$_config['mysql-user'],$_config['mysql-pass'],$_config['mysql-db']);

function getLocation($ip){
    global $includepath;
    $ret = array('cc' => '', 'city' => '');
    if(file_exists($includepath.'/../var/GeoLiteCity.dat')){
        require_once $includepath.'/geoip/geoipcity.inc';
        geoip_load_shared_mem($includepath.'/../var/GeoLiteCity.dat');
        $gi = geoip_open($includepath.'/../var/GeoLiteCity.dat', GEOIP_SHARED_MEMORY);
        $record = GeoIP_record_by_addr($gi, $ip);
        $ret['cc'] = $record->country_code;
        $ret['city'] = $record->city;
        geoip_close($gi);
    }
    return $ret;
}
?>