<?php

$basePath = dirname(dirname(dirname(dirname(__FILE__))));
require_once $basePath.'/lib/common.inc.php';


//TODO: die ganze quotascheiße
require_once('api.inc.php');
$api = new Api();

echo $api->getJson();