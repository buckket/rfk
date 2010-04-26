<?php
require_once('../lib/common-web.inc.php');
$template = new BpTemplate('index_body.html');
include('include/sidebar.php');
include('include/listenercount.php');
cleanup($template);
echo $template->render();
?>
