<?php
require_once(dirname(__FILE__).'/common.inc.php');
$bproot = dirname(dirname(__FILE__));
Beilpuz::$templates = $bproot.'/templates/'.$_config['default_template'];
Beilpuz::$cache = $bproot.'/cache/cache';
Beilpuz::$compiled = $bproot.'/cache/compiled';

?>
