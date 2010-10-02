<?php
require_once('../lib/common-web.inc.php');
global $lang;

$h2o = new H2o('admin.html',$h20settings);
$template = array();
$template['PAGETITLE'] = $lang->lang('L_ADMIN');
$template['section'] = 'admin';
cleanup_h2o($template);
include('include/listenercount.php');
include('include/sidebar.php');

echo $h2o->render($template);
?>
