<?php
$basePath = dirname(dirname(__FILE__));
require_once $basePath.'/lib/common.inc.php';

$sql = 'SELECT TIME_TO_SEC(TIMEDIFF(begin,now())) as t
          FROM shows
         WHERE begin > NOW() order by begin asc LIMIT 1;';

$dbres = $db->query($sql);

if($dbres && $db->num_rows($dbres) > 0) {
    if($row = $db->fetch_assoc($dbres)) {
        if($row['t'] < $_config['preshow_time'] ) { //5 minutes :3
            $filename = $basePath.'/var/music/'.$_config['preshow_loop'];
            if(file_exists($filename)){
                echo $filename;
                exit;
            }
        }
    }
}
$db->free($dbres);

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