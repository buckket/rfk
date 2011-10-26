<?php
/**
 * RfK-Config
 */
$_config['mysql-host'] = 'localhost';
$_config['mysql-db']   = 'radio';
$_config['mysql-user'] = 'radio';
$_config['mysql-pass'] = 'muhmann';

$_config['template'] = 'radio';

$_config['default-username'] = 'Gast';

$_config['icecast_address'] = 'localhost';
$_config['icecast_external'] = '192.168.2.101';
//$_config['icecast_external'] = 'radio.krautchan.net';
$_config['icecast_port'] = 8000;

$_config['liquidsoap_address'] = '192.168.2.101';

$_config['liquidsoap_port'] = 8010;

$_config['preshow_time'] = 7*60; // time before pre show loop in seconds
$_config['preshow_loop'] = 'null';
?>
