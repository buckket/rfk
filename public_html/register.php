<?php
require_once('../lib/common-web.inc.php');
$template = new BpTemplate('register.html');
cleanup($template);
echo $template->render();
?>