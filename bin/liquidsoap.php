<?php
require_once(dirname(__FILE__).'/../lib/common.inc.php');
//error_reporting(0); // disable error reporting
$mode = $argv[1];
$sql = "INSERT INTO debuglog (time,text) VALUES (NOW(),'".$db->escape(serialize($argv))."');";
$db->execute($sql);
switch($mode){

    case 'auth':
        if(handleAuth($argv[2],$argv[3])){
            echo 'true';
        }else{
            echo 'false';
        }
        break;
    case 'connect':
        handleConnect($argv[2]);
        break;
    case 'disconnect':
        $sql = "UPDATE songhistory SET end = NOW() WHERE end IS NULL;";
        $db->execute($sql);
        $sql = "UPDATE shows SET end = NOW() WHERE showtype = 'UNPLANNED' AND end IS NULL;";
        $db->execute($sql);
        $sql = "UPDATE streamer SET status = 'NOT_CONNECTED' WHERE status = 'STREAMING';";
        $db->execute($sql);
        break;
    case 'meta':
        handleMetaData($argv[2]);
        break;
}

function handleAuth($username,$password){
    global $db;
    fixSimpleClientAuth($username,$password);
    $sql = "SELECT streamer FROM streamer WHERE username = '".$db->escape($username)."' AND streampassword = '".$db->escape($password)."' LIMIT 1;";
    $result = $db->query($sql);
    if($db->num_rows($result) > 0 ){
        $user = $db->fetch($result);
        $sql = "UPDATE streamer SET status = 'LOGGED_IN' WHERE streamer = '".$user['streamer']."' AND status = 'NOT_CONNECTED';";
        $db->execute($sql);
        return true;
    }else{
        return false;
    }
}

function handleConnect($data){
    global $db;
    $sql = "LOCK TABLES streamer WRITE;";
    $db->execute($sql);
    $meta = serializedToArray($data);
    list($authtype, $authcred) = split(" ", $meta['authorization'], 2);
    list($username, $password) = split(":", base64_decode($authcred) , 2);
    fixSimpleClientAuth($username,$password);
    $sql = "UPDATE streamer
            SET status = 'STREAMING'
            WHERE status = 'LOGGED_IN'
            AND username = '".$db->escape($username)."'
            AND streampassword = '".$db->escape($password)."'
            LIMIT 1;";
    $db->execute($sql);
    $sql = "UPDATE streamer
            SET status = 'NOT_CONNECTED'
            WHERE status = 'LOGGED_IN';";
    $db->execute($sql);
    $sql = "UNLOCK TABLES;";
    $db->execute($sql);
}

function handleMetaData($metadata){
    global $db;
    $meta = serializedToArray($metadata);
    if(isset($meta['artist']) || isset($meta['title']) || isset($meta['song'])) {
        $songinfo = array();
        $meta['song'] = trim($meta['song']);
        $meta['artist'] = trim($meta['artist']);
        $meta['title'] = trim($meta['title']);
        if(strlen($meta['artist']) == 0){
            if(strlen($meta['title']) == 0){
                $meta['title'] = $meta['song'];
            }
            $tmp = preg_split('/ - /',$meta['title'],2);
            if(strlen(trim($tmp[1])) == 0){
                $tmp[1] = $tmp[0];
                $tmp[0] = '';
            }
            $songinfo['artist'] = $tmp[0];
            $songinfo['title'] = $tmp[1];
        }else{
            $songinfo['artist'] = $meta['artist'];
            $songinfo['title'] = $meta['title'];
        }
        $songinfo['show'] = checkShow(getUserID());
        $sql = "UPDATE songhistory SET end = NOW() WHERE end IS NULL;";
        $db->execute($sql);
        $sql = "INSERT INTO songhistory (start,artist,title,userid) VALUES (NOW(),'".$db->escape($songinfo['artist'])."','".$db->escape($songinfo['title'])."','".$db->escape($songinfo['dj'])."','".$db->escape($songinfo['show'])."')";
        $db->execute($sql);
    }
}

function getUserID(){
    global $db;
    $sql = "SELECT userid FROM streamer WHERE status = 'STREAMING' LIMIT 1;";
    $result = $db->query($sql);
    $userid = 0;
    if($db->num_rows($result) > 0){
        $id = $db->fetch($result);
        $userid = $id['userid'];
    }
    return $userid;
}

function checkShow(){
    global $db;
    $sql = "SELECT show, type
            FROM streamer JOIN shows USING ( streamer )
            WHERE streamer.status = 'STREAMING'
            AND (shows.end IS NULL
                 OR NOW() BETWEEN shows.begin AND shows.end)";
    $result = $db->query($sql);
    if($db->num_rows($result) > 0){
        $stype = '';
        $pshowid = false;
        while( $show = $db->fetch($result) ){
            if(($show['type'] == 'PLANNED' && $stype == 'UNPLANNED') ||
               ($show['type'] == 'UNPLANNED' && $stype == 'PLANNED')) {
                //end existing unplanned show
                $sql = "UPDATE shows SET end = NOW() WHERE showtype = 'UNPLANNED' AND end IS NULL;";
                $db->execute($sql);
            }
            if ($stype != $show['type']) {
                $stype = $show['type'];
            }
            if( $show['type'] == 'PLANNED' ) {
                $pshowid = $show['id'];
            }
        }
        return $pshowid;
    } else {
        $sql = "INSERT INTO shows (userid,name,description,begin,end,showtype)
                    SELECT $userid,defaultshowname,'',NOW(),NULL,'UNPLANNED'
                    FROM streamer
                    WHERE userid = $userid;";
        $db->execute($sql);
        return $db->insert_id();
    }
}


//global functions
function serializedToArray($string){
    $array = array();
    if(preg_match_all("/(.*?)='(.*?)'(;|$)/",$string,$matches,PREG_SET_ORDER)){
        foreach($matches as $entry){
            $array[$entry[1]] = $entry[2];
        }
        return $array;
    } else {
        return false;
    }
}
//Fix for clients who always use source as username
function fixSimpleClientAuth(&$username,&$password){
    if(strtolower(trim($username)) === 'source' || strlen(trim($username)) == 0){
        //edcast, etc ... which cant change username
        $cred = preg_split('/\\|/',$password,2);
        $username = $cred[0];
        $password = $cred[1];
    }else{
        //cool clients
        //stub - maybe we need something here
    }
}

?>
