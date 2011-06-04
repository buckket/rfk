<?php
require_once('../lib/common-web.inc.php');
global $lang;
$template = array();

// if admin set flag to show edit form
$template['isAdmin'] = $user->is_admin();


$sql = "SELECT * FROM `donations`
        GROUP BY `donation` DESC
        LIMIT 30;";
$dbres = $db->query($sql);
$donations = array();
if($dbres) {
    while($row = $db->fetch($dbres)) {
        $row['country'] = checkCB($row['country']);
        $row['time'] = date("d.m.Y",strtotime ($row['time']));
        $donations[] = $row;
    }
    $template['donations'] = $donations;
}


$template['PAGETITLE'] = $lang->lang('L_DONATIONS');
$template['section'] = 'donations';
cleanup_h2o($template);
include('include/listenercount.php');
include('include/sidebar.php');


$h2o = new H2o('donations.html',$h2osettings);
echo $h2o->render($template);
?>