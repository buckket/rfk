<?php
include('../../../lib/common-web.inc.php');
$sql = "SELECT artist,title,shows.name,streamer.username
        FROM songhistory
        JOIN shows USING (`show`)
        JOIN streamer USING (`streamer`)
        WHERE songhistory.end IS NULL;";

$dbres = $db->query($sql);
echo json_encode($db->fetch_assoc($dbres));

?>