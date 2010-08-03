<?php
require_once('../lib/common-web.inc.php');
$template = array();
$template['PAGETITLE'] = 'Verlauf';
$template['section'] = 'history';
cleanup_h2o($template);
include('include/listenercount.php');
include('include/sidebar.php');
$h2o = new H2o('history.html',$h2osettings);
echo $h2o->render($template);
?>
