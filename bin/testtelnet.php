<?php
$basePath = dirname(dirname(__FILE__));
require_once $basePath.'/lib/common.inc.php';
require_once $basePath.'/lib/liquidsoaptelnet.php';

$tn = new Liquidsoap();
$tn->connect();
$tn->getHarborSource();
$tn->kickHarbor();
?>