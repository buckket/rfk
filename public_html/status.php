<?php
require_once('../lib/common-web.inc.php');
$template = new BpTemplate('status.html');
include('include/sidebar.php');
cleanup($template);
echo $template->render();
?>