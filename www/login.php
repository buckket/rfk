<?php
require_once('../lib/common-web.inc.php');
$template = new BpTemplate('login.html');
include('include/listenercount.php');
cleanup($template);
$template->assign('PAGETITLE', "Einloggen");
echo $template->render();

?>
