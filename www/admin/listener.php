<?php
global $lang;
$template = array();
require_once('admin-common.inc.php');
$template['PAGETITLE'] = $lang->lang('L_OVERVIEW');
$sql = "SELECT listenerhistory, INET_NTOA(ip) as ip,city,country,useragent
        FROM listenerhistory
        WHERE disconnected IS NULL";

$dbres = $db->query($sql);

if($dbres){
    while($row = $db->fetch($dbres)){
        $template['listeners'][] = array('id' => $row['listenerhistory'],
                                      'country' => $row['country'],
                                      'city'    => $row['city'],
                                      'ip'      => $row['ip'],
                                      'ua' => $row['useragent']);
    }
}
cleanup_h2o($template);
$h2o = new H2o('admin/listener.html',$h2osettings);
echo $h2o->render($template);

?>