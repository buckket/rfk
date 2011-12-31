<?php
require_once(dirname(__FILE__).'/../../lib/common.inc.php');
require_once(dirname(__FILE__).'/../include/listenercount.php');

echo '<icestats><source mount="/listener"><Listeners>'.$template['listenercount'].'</Listeners></source></icestats>';
?>