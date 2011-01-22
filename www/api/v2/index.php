<?php

//TODO: die ganze quotascheiße
require_once('api.inc.php');
$api = new Api();

echo $api->getJson();