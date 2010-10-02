<?php
$template = array();

require_once('admin-common.inc.php');
$template['PAGETITLE'] = $lang->lang('L_ADMIN').': '.$lang->lang('L_OVERVIEW');
cleanup_h2o($template);
$h2o = new H2o('admin/index.html',$h2osettings);
echo $h2o->render($template);

?>