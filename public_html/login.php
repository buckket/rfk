<?php
require_once('../lib/common-web.inc.php');
$template = new BpTemplate('login.html');
cleanup($template);
echo $template->render();

?>