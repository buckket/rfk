<?php
require_once('../lib/common-web.inc.php');
$template = new BpTemplate('dummy.html');
include('include/listenercount.php');
cleanup($template);
$template->assign('PAGETITLE', "Verlauf");
echo $template->render();

?>
