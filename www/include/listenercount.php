<?php

$sql = "SELECT count(*) as count FROM listenerhistory WHERE disconnected IS NULL";
$result = $db->query($sql);
$row = $db->fetch($result);
$template->assign('LISTENERCOUNT', $row['count']);

?>