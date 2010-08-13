<?php
echo long2ip(1292505259);
echo "\n";
$array = getLocation(long2ip(1292505259));
echo utf8_encode($array['city']);
echo "\n";
echo 'ü';
echo "\n";
function getLocation($ip){
    global $includepath;
    $ret = array('cc' => '', 'city' => '');
    if(file_exists('../var/GeoLiteCity.dat')){
        require_once '../lib/geoip/geoipcity.inc';
        geoip_load_shared_mem('../var/GeoLiteCity.dat');
        $gi = geoip_open('../var/GeoLiteCity.dat', GEOIP_SHARED_MEMORY);
        $record = GeoIP_record_by_addr($gi, $ip);
        $ret['cc'] = $record->country_code;
        $ret['city'] = $record->city;
        geoip_close($gi);
    }
    return $ret;
}