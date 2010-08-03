<?php
require_once('../lib/common-web.inc.php');
$template = array();
$template['PAGETITLE'] = 'Übersicht';
$template['section'] = 'overview';
cleanup_h2o($template);
include('include/listenercount.php');
include('include/sidebar.php');
$h2o = new H2o('index.html',$h2osettings);
echo $h2o->render($template);
?>
