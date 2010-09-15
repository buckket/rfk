<?php
require_once('../lib/common-web.inc.php');
$template = array();
$fulltemplate = false;
if(!(isset($_GET['ajax']) && $_GET['ajax'] == 'true')) {
    //load complete page
    include('include/listenercount.php');
    include('include/sidebar.php');
    $fulltemplate = true;
}
// the content
if(isset($_GET['action']) && $_GET['action'] == 'edit') {
    if(isset($_GET['show']) && strlen($_GET['show'])>0){
        if($user->logged_in){
            $shows = explode(',',$_GET['show']);
            $sql = 'SELECT * FROM shows WHERE `show` IN ('.$db->escape(implode(',',$shows)).') AND streamer = '.$user->userid.';';
            $dbres = $db->query($sql);
            if($db->num_rows($dbres) == 1){
                $show = $db->fetch($dbres);
                $template['show'] = $show;
                showPage($template, 'editshow', false);
            }else if($db->num_rows($dbres) > 1){
                while($show = $db->fetch($dbres)){
                    $template['shows'][] = $show;
                }
                showPage($template, 'selectshow', $fulltemplate);
            }
        }else{
            $template['error'] = 'notloggedin';
        }
    }
}else if(isset($_GET['show']) && strlen($_GET['show'])>0){
    $shows = explode(',',$_GET['show']);
    $sql = 'SELECT `show`,description, name, UNIX_TIMESTAMP(begin) as begin, UNIX_TIMESTAMP(end) as end, streamer FROM shows WHERE `show` IN ('.$db->escape(implode(',',$shows)).')';
    $dbres = $db->query($sql);
    while($show = $db->fetch($dbres)){
        $show['description'] = $bbcode->parse($show['description']);
        $sql = "Select * FROM songhistory WHERE `show` = ".$show['show']." order by begin desc;";
        $res = $db->query($sql);
        while($song = $db->fetch($res)){
            $show['songs'][] = $song;
        }
        $template['shows'][] = $show;
    }
    showPage($template, 'viewshow', $fulltemplate);
}
//print_r($template);

function showPage($template, $templatename,$full){
    global $h2osettings;
    cleanup_h2o($template);
    $h2o = new H2o($templatename.($full?'-full':'').'.html',$h2osettings);
    echo $h2o->render($template);
}