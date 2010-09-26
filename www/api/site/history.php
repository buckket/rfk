<?php
include('../../../lib/common-web.inc.php');

$limit = array();

$output = array();
if(isset( $_GET['iDisplayStart'])) {
    $limit = array($db->escape($_GET['iDisplayStart']), $db->escape($_GET['iDisplayLength']));
}
$sql = "SELECT SQL_CALC_FOUND_ROWS songhistory.begin, username, name, artist, title
        FROM songhistory
        JOIN shows USING (`show`)
        JOIN streamer USING ( streamer )
        ";
$sql .= " ORDER BY songhistory.begin desc";
if(count($limit)> 0){
    $sql .=" LIMIT ".implode(', ',$limit);
}


$dbres = $db->query($sql);
$i = 0;
while($row = $db->fetch($dbres)) {
    $output['aaData'][] = array($row['begin'],$row['username'],$row['name'],$row['artist'],$row['title']);
    $i++;
}
$output['iTotalRecords'] = $db->getFoundRows();
$output['iTotalDisplayRecords'] = $output['iTotalRecords'];
$output['sEcho'] = intval($_GET['sEcho']);
echo json_encode($output);

?>