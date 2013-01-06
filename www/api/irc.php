<?php
$basePath = dirname(dirname(dirname(__FILE__)));

require_once $basePath.'/lib/common.inc.php';
require_once $basePath.'/lib/liquidsoaptelnet.php';
require_once $basePath.'/lib/api.inc.php';

printf("<h1>#RfK - Niemand will's wissen</h1>");

global $db;
$sql = "SELECT * FROM streamersettings WHERE `key` = 'isIRC' AND value = 1";
$dbres = $db->query($sql);
if($dbres) {
    while($row = $db->fetch($dbres)) {
        $sql = "SELECT * FROM streamersettings JOIN streamer using(streamer) WHERE `key` = 'hostmask' AND streamer = $row[streamer]";
        $dbres2 = $db->query($sql);
        while($row = $db->fetch($dbres2)) {
            printf("%s ist mit folgender hostmask im IRC: %s <br>\n", $row['username'], $row['value']);
        }
    }
}
printf("<br>\n<img src='../irc.png' />");
printf("\n<img src='../ircw.png' />");
printf("<br>\n<img src='../ircl.png' />");
printf("\n<img src='../irclw.png' />");
printf("<br>\n<br>\nMomentan: %s IRC-Bernds", getIRCCount());
?>
