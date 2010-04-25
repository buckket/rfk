<?php
require_once('../lib/common-web.inc.php');
$template = new BpTemplate('status.html');
include('include/sidebar.php');

$sql = "SELECT mount,description,count FROM (SELECT count(*) as count,mountid FROM listenerhistory WHERE disconnected IS NULL group by mountid) as l RIGHT JOIN mounts USING ( mountid)";
$result = $db->query($sql);
$streams = array();
while($row = $db->fetch($result)){
    if(strlen($row['count']) == 0){
        $row['count'] = 0;
    }
    $streams[] = $row;
}
$template->assign('streams',$streams);
cleanup($template);
echo $template->render();
?>