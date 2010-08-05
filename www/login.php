<?php
require_once('../lib/common-web.inc.php');
$template = array();
include('include/sidebar.php');
include('include/listenercount.php');
$template['section'] = "login";
$template['PAGETITLE'] = "Einloggen";
cleanup_h2o($template);
$h2o = new H2o('login.html',$h2osettings);
echo $h2o->render($template);

?>
