<?php
$basePath = dirname(dirname(__FILE__));
require_once $basePath.'/lib/common.inc.php';

$sql = 'SELECT path
FROM playlist
WHERE CURTIME( )
BETWEEN `from`
AND `to`
ORDER BY `from` DESC';
$dbres = $db->query($sql);
if ($dbres && $db->num_rows($dbres) > 0) {
    $row = $db->fetch($dbres);
    $filename = $basePath.'/var/music/'.$row['path'];
    if(file_exists($filename)){
        echo $filename;
        exit;
    }
}
echo $basePath.'/var/music/loop.ogg';
?>