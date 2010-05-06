<?php
require_once('../lib/common-web.inc.php');


$h2o = new H2o('admin.html',$h20settings);
$template = array();
$template['PAGETITLE'] = 'Admin';
$template['section'] = 'admin';
cleanup_h2o($template);
include('include/listenercount.php');
include('include/sidebar.php');
print_r($template);
echo $h2o->render($template);
?>
