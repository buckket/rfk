<?php
/**
 *  Common includes/requires for everything
 *  
 *  Website-commons doesn't belong here
 *  this is also used by consoledriven scripts
 */
$includepath = dirname(__file__);
require_once($includepath.'/config.inc.php');
require_once($includepath.'/classes/dbi.php');

$db = new DBI($_config['mysql-host'],$_config['mysql-user'],$_config['mysql-pass'],$_config['mysql-db']);
?>