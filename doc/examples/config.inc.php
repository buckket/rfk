<?php
/**
 * RfK-Config
 */
//database credentials
$_config['mysql-host'] = 'localhost';
$_config['mysql-db']   = 'radiodb';
$_config['mysql-user'] = 'dbuser';
$_config['mysql-pass'] = 'dbpassword';

//template to use
$_config['template'] = 'radio';

//name of the not logged in user
$_config['default-username'] = 'Gast';

//address of the streaming server
$_config['icecast_address'] = '172.0.0.1';
$_config['icecast_port'] = 8000;

//addess for liquidsoap
$_config['liquidsoap_address'] = '172.0.0.1';
$_config['liquidsoap_port'] = 8010;

//proshow loop config
$_config['preshow_time'] = 7*60; // time before pre show loop in seconds
$_config['preshow_loop'] = 'null';

$_config['base'] = '/path_to/rfk';
//lastfm credentials set to null if disabled
$_config['lastfm'] = array('username','password');

//absolut path to record archive
$_config['recorddir'] = '/tmp/';

$_config['torrent_dir'] = '/home/radio/torrents/'; //absolut path to torrent archive
$_config['torrent_announce'] = 'http://radio.krautchan.net:6969/announce';

$_config['tracker_pidfile'] = '/home/radio/opentracker/opentracker.pid';
$_config['tracker_whitelist'] = '/home/radio/opentracker/whitelist';

?>
