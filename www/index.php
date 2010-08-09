<?php
require_once('../lib/common-web.inc.php');
$template = array();
$template['PAGETITLE'] = 'Übersicht';
$template['section'] = 'overview';
$sql = "SELECT news, time, description, text, username
        FROM news
        JOIN streamer USING (streamer)
        ORDER BY time
        DESC LIMIT 5;";
$dbres = $db->query($sql);
if($dbres){
    while($row = $db->fetch($dbres)){
        $row['text'] = $bbcode->parse($row['text']);

        $template['news'][] = array('id' => $row['news'],
                                    'description' => $row['description'],
                                    'user' => $row['username'],
                                    'text' => $row['text'],
                                    'time' => $row['time']);
    }
}
cleanup_h2o($template);
include('include/listenercount.php');
include('include/sidebar.php');
$h2o = new H2o('index.html',$h2osettings);
echo $h2o->render($template);
?>
