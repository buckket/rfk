<?php
require_once('../lib/common-web.inc.php');
$template = array();
$template['PAGETITLE'] = 'Verlauf';
$template['section'] = 'history';
cleanup_h2o($template);
include('include/listenercount.php');
include('include/sidebar.php');
$sql = "SELECT DATE_FORMAT(begin,'%d.%m.%Y') as b FROM songhistory group by b order by begin desc";
$dbres = $db->query($sql);
$dates = array();
while($row = $db->fetch($dbres)) {
    $dates[] = $row['b'];
}
$template['dates'] = $dates;
$h2o = new H2o('history.html',$h2osettings);
echo $h2o->render($template);
?>
