<?php
require_once('../lib/common.inc.php');

$mode = $argv[1];

switch($mode){

    case 'auth':
        echo 'true';
        break;
    case 'connect':
        break;
    case 'disconnect':
        break;
    case 'meta':
        break;

}
?>