<?php
$basePath = dirname(dirname(__FILE__));
require_once $basePath.'/lib/common.inc.php';
require_once $basePath.'/lib/api.inc.php';
$out = array();

getCurrShow($out);
getCurrTrack($out);
getNextShows($out);
getDJ($out);
$data = json_encode($out);
file_put_contents($basePath.'/www/ram/kc.json', $data);
?>