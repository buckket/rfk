<?php
require_once('../lib/common-web.inc.php');
$template = new H2o('admin.html',$h20settings);
include('include/listenercount.php');
cleanup($template);
echo $h2o->render(array('PAGETITLE'=>'Admin','section' => 'login'));
?>
