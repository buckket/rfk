<?php
/**
 * RfK-Config
 */
//base dir in filesystem mostyl used to find loopmusig and set paths for liquidsoap
$_config['base'] = '/home/teddydestodes/src/rfk/';


$_config['mysql-host'] = 'localhost';
$_config['mysql-db']   = 'radio';
$_config['mysql-user'] = 'radio';
$_config['mysql-pass'] = 'radiowegbuxen';

$_config['template'] = 'radio';

$_config['default-username'] = 'Gast';

$_config['icecast_address'] = '192.168.2.101';
$_config['icecast_external'] = '192.168.2.101';
//$_config['icecast_external'] = 'radio.krautchan.net';
$_config['icecast_port'] = 8000;
$_config['icecast_pass'] = 'hackme';

$_config['liquidsoap_address'] = '192.168.2.134';

$_config['liquidsoap_port'] = 8080;

$_config['pagetitle'] = 'Radio freies Krautchan';
$_config['webroot'] = '/rfk/';

?>
