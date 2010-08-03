<?php
session_start();

require_once(dirname(__FILE__).'/common.inc.php');
require_once(dirname(__FILE__).'/common-functions.inc.php');
require_once(dirname(__FILE__).'/user.php');
require_once(dirname(__FILE__).'/h2o/h2o.php');

//set Paths for Beilpuz (somewhat un(crappy)documented feature)
$root = dirname(dirname(__FILE__));
$h2osettings = array('searchpath' => $root.'/var/templates/'.$_config['template'].'new',
                     'cache'      => false);
//the User
$user = new USER();
//global arrays for messages
$_MSG['err'] = array();
$_MSG['warn'] = array();
$_MSG['msg'] = array();
?>
