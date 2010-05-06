<?php

$sql = "SELECT count(*) as count FROM listenerhistory WHERE disconnected IS NULL";
$result = $db->query($sql);
$row = $db->fetch($result);
if(is_array($template)){
	$template['listenercount'] = $row['count'];
}else{
	$template->assign('LISTENERCOUNT', $row['count']);
}
?>