<?php
require_once(dirname(__FILE__).'/../lib/common.inc.php');
error_reporting(0); // disable error reporting
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
        $sql = "UPDATE shows SET end = NOW() WHERE type = 'UNPLANNED' AND end IS NULL;";
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
    $sql = "SELECT streamer, IF(ban > NOW(), 'ban', IF(ban IS NULL, 'notbanned', 'expired')) as bstat
            FROM streamer
            WHERE username = '".$db->escape($username)."'
              AND streampassword = '".$db->escape($password)."'
              LIMIT 1;";
    $result = $db->query($sql);
    if($db->num_rows($result) > 0 ){
        $user = $db->fetch($result);
        if($user['bstat'] != 'ban') {
            autoKick($user['streamer']);
            $sql = "UPDATE streamer SET status = 'LOGGED_IN', ban = NULL WHERE streamer = '".$user['streamer']."' AND status = 'NOT_CONNECTED';";
            $db->execute($sql);
            return true;
        }
    }
    return false;
}

function autoKick($user){
    global $db,$includepath;
    $sql = "SELECT * FROM shows WHERE streamer = $user AND NOW() BETWEEN begin AND end AND type = 'PLANNED'";
    $dbres = $db->query($sql);
    if($db->num_rows($dbres) > 0) {
        $sql = "SELECT * FROM streamer WHERE status = 'STREAMING'";
        $dbres = $db->query($sql);
        if($row = $db->fetch($dbres)) {
            if($row['streamer'] != $user){
                require_once $includepath.'/liquidsoaptelnet.php';
                $liquid = new Liquidsoap;
                $liquid->connect();
                $liquid->getHarborSource();
                $liquid->kickHarbor();
            }
        }
    }
    $db->free($dbres);
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
    $sql = "SELECT *
            FROM streamersettings
            JOIN streamer USING (streamer)
            WHERE status = 'STREAMING'
              AND `key` = 'icytags'";
    $dbres = $db->query($sql);
    if($dbres && $db->num_rows($dbres) > 0) {
        if(isset($meta['ice-name'])) {
            $sql = 'INSERT INTO streamersettings (streamer,`key`,value)
                        SELECT streamer,"icyshowname","'.$db->escape($meta['ice-name']).'"
                        FROM streamer WHERE status = "STREAMING"
                    ON DUPLICATE KEY UPDATE value = "'.$db->escape($meta['ice-name']).'";';
            $db->execute($sql);
        }
        if(isset($meta['ice-description'])) {
            $sql = 'INSERT INTO streamersettings (streamer,`key`,value)
                        SELECT streamer,"icyshowdescription","'.$db->escape($meta['ice-description']).'"
                        FROM streamer WHERE status = "STREAMING"
                    ON DUPLICATE KEY UPDATE value = "'.$db->escape($meta['ice-description']).'";';
            $db->execute($sql);
        }
    }

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
        if ($songinfo['show'] = checkShow()){
            $sql = "UPDATE songhistory SET end = NOW() WHERE end IS NULL;";
            $db->execute($sql);
            $sql = "INSERT INTO songhistory (`show`,begin,artist,title) VALUES (".$songinfo['show'].",NOW(),'".$db->escape($songinfo['artist'])."','".$db->escape($songinfo['title'])."')";
            $db->execute($sql);
        }
    }else{
        //fix für die idioten die nichts mitsenden
        checkShow();
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
    $sql = "SELECT `show`, type
            FROM streamer JOIN shows USING ( streamer )
            WHERE streamer.status = 'STREAMING'
            AND (shows.end IS NULL
                 OR NOW() BETWEEN shows.begin AND shows.end)";
    $result = $db->query($sql);
    if($db->num_rows($result) > 0){
        $upshowid = false;
        $pshowid = false;
        while( $show = $db->fetch($result) ){
            //print_r($show);
            if ($show['type'] == 'UNPLANNED') {
                $upshowid = $show['show'];
            }
            if( $show['type'] == 'PLANNED' ) {
                $pshowid = $show['show'];
            }
        }
        if($pshowid > 0 ){
            if($upshowid > 0){
                $sql = "UPDATE shows SET end = NOW() WHERE end IS NULL;";
                $db->query($sql);
            }
            return $pshowid;
        }else{
            return $upshowid;
        }

    } else {
        $showname = '';
        $showdescription = '';
        $sql = "SELECT *
            FROM streamersettings
            JOIN streamer USING (streamer)
            WHERE status = 'STREAMING'
              AND `key` = 'icytags'";
        $dbres = $db->query($sql);
        if($dbres && $db->num_rows($dbres) > 0) {
            $sql = "SELECT `key`, value
                    FROM streamersettings
                    JOIN streamer USING (streamer)
                    WHERE status = 'STREAMING'
                      AND `key` IN ('icyshowname', 'icyshowdescription');";
            $res = $db->query($sql);
            while($row = $db->fetch($res)) {
                switch($row['key']){
                    case 'icyshowname':
                            $showname = $row['value'];
                        break;
                    case 'icyshowdescription':
                            $showdescription = $row['value'];
                        break;
                }
            }
        } else {
            $sql = "SELECT `key`, value
                    FROM streamersettings
                    JOIN streamer USING (streamer)
                    WHERE status = 'STREAMING'
                      AND `key` IN ('defaultshowname', 'defaultshowdescription');";
            $res = $db->query($sql);
            while($row = $db->fetch($res)) {
                switch($row['key']){
                    case 'defaultshowname':
                        $showname = $row['value'];
                        break;
                    case 'defaultshowdescription':
                        $showdescription = $row['value'];
                        break;
                }
            }
        }
        $sql = "UPDATE shows SET end = NOW() WHERE end IS NULL;";
                $db->query($sql);
        $sql = "INSERT INTO shows (streamer, name, description, begin, type)
                SELECT streamer, '".$db->escape($showname)."', '".$db->escape($showdescription)."', NOW(), 'UNPLANNED'
                FROM streamer
                WHERE STATUS = 'STREAMING'";
        if( $db->execute($sql) ){
            return $db->insert_id();
        }else {
            return false;
        }
        return false;
    }
    return false;
}


//global functions
function serializedToArray($string){
    $array = array();
    if(preg_match_all("/(.*?)='(.*?)'(;|$)/m",$string,$matches,PREG_SET_ORDER)){
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
