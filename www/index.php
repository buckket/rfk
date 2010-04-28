<?php
require_once('../lib/common-web.inc.php');
$template = new BpTemplate('index_body.html');
include('include/sidebar.php');
include('include/listenercount.php');
$template->assign('PAGETITLE', "&Uuml;bersicht");
cleanup($template);
echo $template->render();
?>
