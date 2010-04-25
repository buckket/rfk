<?php
require_once('../lib/common-web.inc.php');
$template = new BpTemplate('dummy.html');
cleanup($template);
echo $template->render();

?>