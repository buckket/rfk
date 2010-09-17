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
            $sql = 'SELECT `show`, DATE_FORMAT(begin,"%d.%m.%Y") as `date`, description, name, TIME_TO_SEC(TIMEDIFF(end,begin)) as length, UNIX_TIMESTAMP(begin) AS begin FROM shows WHERE `show` IN ('.$db->escape(implode(',',$shows)).') AND streamer = '.$user->userid.';';
            $dbres = $db->query($sql);
            if($db->num_rows($dbres) == 1){
                $show = $db->fetch($dbres);
                $template['show'] = $show;
                $begin = getdate($show['begin']);
                $begin = ($begin['hours']*2)+round($begin['minutes']/30);
                for($i = 0; $i < 48; $i++){
                    if($begin == $i){
                        $template['start'][] = array( 'value' => $i ,'val' =>floor($i/2).':'.($i%2==0?'00':'30'), 'selected' => true);
                    }
                    $template['start'][] = array( 'value' => $i ,'val' => floor($i/2).':'.($i%2==0?'00':'30'));
                }
                for($i = 1; $i < 48; $i++){
                    if(round($show['length']/1800) == $i){
                        $template['length'][] = array( 'value' => $i ,'val' =>floor($i/2).':'.($i%2==0?'00':'30'), 'selected' => true);
                    }
                    $template['length'][] = array( 'value' => $i , 'val' => floor($i/2).':'.($i%2==0?'00':'30'));
                }
                showPage($template, 'editshow', false);
            }else{
                echo 'blah';
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
        $sql = "Select * FROM songhistory WHERE `show` = ".$show['show']." order by begin asc;";
        $res = $db->query($sql);
        while($song = $db->fetch($res)){
            $show['songs'][] = $song;
        }
        if($user->logged_in && $user->userid == $show['streamer']) {
            $show['editable'] = true;
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