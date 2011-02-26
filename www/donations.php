<?php
require_once('../lib/common-web.inc.php');
global $lang;
$template = array();
$template['PAGETITLE'] = $lang->lang('L_DONATIONS');
$template['section'] = 'donations';
cleanup_h2o($template);
include('include/listenercount.php');
include('include/sidebar.php');


$h2o = new H2o('donations.html',$h2osettings);
echo $h2o->render($template);
?>