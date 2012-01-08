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
        $sql = "SELECT titleid, UNIX_TIMESTAMP(begin) as begin, UNIX_TIMESTAMP(NOW()) as end FROM songhistory WHERE end IS NULL";
        $dbres = $db->query($sql);
        if($dbres && $db->num_rows($dbres) > 0) {
            if($lastsong = $db->fetch($dbres)) {
                $sql = "UPDATE songhistory SET end = NOW() WHERE end IS NULL;";
                $db->execute($sql);
                updateTitleLength($lastsong['titleid'], $lastsong['end'] - $lastsong['begin']);

            }
        }
        $sql = "UPDATE shows SET end = NOW() WHERE type = 'UNPLANNED' AND end IS NULL;";
        $db->execute($sql);
        $sql = "UPDATE streamer SET status = 'NOT_CONNECTED' WHERE status = 'STREAMING';";
        $db->execute($sql);
        recordStopHook();
        break;
    case 'meta':
        handleMetaData($argv[2]);
        break;
    case 'record':
        if($argv[2] === 'start') {
            startRecording();
        } else if ($argv[2] === 'stop') {
            stopRecording();
        } else if ($argv[2] === 'finish'){
            finishRecording($argv[3]);
        }
        break;
}
exit(0);

function startRecording() {
    global $db;
    require_once(dirname(__FILE__).'/../lib/LiquidInterface.php');
    $liquid = new LiquidInterface();
    $liquid->connect();
    $output = $liquid->getOutputStreams();
    if(in_array('recordstream', $output)) {
        if($liquid->getOutputStreamStatus('recordstream') === 'off') {
            $sql = "SELECT `show`, type
                      FROM streamer JOIN shows USING ( streamer )
                     WHERE streamer.status = 'STREAMING'
                       AND type = 'PLANNED'
                       AND (shows.end IS NULL
                        OR NOW() BETWEEN shows.begin AND shows.end)";
            $dbres = $db->query($sql);
            if($dbres && $db->num_rows($dbres) > 0) {
                $sql = "UPDATE recordings SET status = 'FAILED' WHERE status = 'RECORDING'";
                $db->execute($sql);
                $show = $db->fetch($dbres);
                $sql = "INSERT INTO recordings (`show`,status) VALUES (".$db->escape($show['show']).",'RECORDING')";
                $db->execute($sql);
                $liquid->startOutputStream('recordstream');
            }
        } else {
            return 2;
        }
    } else {
        return 1;
    }
}

function finishRecording($tmpfile) {
    global $db, $_config;
    $filename = $tmpfile;
    rename($filename,'/tmp/tmp.mp3');
    if($_config['record_auto']) { //try to restart with a new show
        if($show = checkShow()) {
            if($show['type'] == 'PLANNED') {
                recordStartHook($show['show']);
            }
        }
    }
    $sql = "SELECT `show`, recording FROM recordings WHERE status = 'RECORDING' ORDER by recording ASC LIMIT 1;";
    $dbres = $db->query($sql);
    if($dbres && $db->num_rows($dbres) == 1) {
        $s = $db->fetch($dbres);
        rename('/tmp/tmp.mp3',$_config['recorddir'].$s['show'].'.mp3');
        $sql = "UPDATE recordings
                   SET status = 'FINISHED',
                       filename = '".$db->escape($s['show'].'.mp3')."'
                 WHERE recording = ".$s['recording']."
                 LIMIT 1;";
        $db->execute($sql);
    }
}

function stopRecording() {
    require_once(dirname(__FILE__).'/../lib/LiquidInterface.php');
    $liquid = new LiquidInterface();
    $liquid->connect();
    $output = $liquid->getOutputStreams();
    if(in_array('recordstream', $output)) {
        if($liquid->getOutputStreamStatus('recordstream') === 'on') {
            $liquid->stopOutputStream('recordstream');
        } else {
            return 2;
        }
    } else {
        return 1;
    }
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
    $sql = "SELECT * FROM shows WHERE streamer = ".$db->escape($user)." AND NOW() BETWEEN begin AND end AND type = 'PLANNED'";
    $dbres = $db->query($sql);
    if($db->num_rows($dbres) > 0) {
        $sql = "SELECT * FROM streamer WHERE status = 'STREAMING'";
        $dbres = $db->query($sql);
        if($row = $db->fetch($dbres)) {
            if($row['streamer'] != $user){
                require_once $includepath.'/LiquidInterface.php';
                $liquid = new LiquidInterface();
                $liquid->connect();
                $liquid->kickHarbor($liquid->getHarborSource());
            }
        }
    }
    $db->free($dbres);
}

function handleConnect($data){
    global $db;
    $sql = "LOCK TABLES streamer WRITE;";
    $db->execute($sql);
    $meta = json_decode($data,true);
    //error_log(print_r($meta,true));
    if(isset($meta['Authorization'])) {
        $meta['authorization'] = $meta['Authorization'];
    }
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
    checkShow(); // try to create a show while there have been no tags send at all
}

function handleMetaData($metadata){
    global $db;
    $meta = json_decode($metadata,true);
    if(isset($meta['artist']) || isset($meta['title']) || isset($meta['song'])) {
        $songinfo = array();
        if(isset($meta['song'])) {
            $meta['song'] = trim($meta['song']);
        }
        if(isset($meta['artist'])) {
            $meta['artist'] = trim($meta['artist']);
        } else if(isset($meta['band'])) { // metatag mapping
            $meta['artist'] = trim($meta['band']);
        }
        if(isset($meta['title'])) {
            $meta['title'] = trim($meta['title']);
        }

        if(!isset($meta['artist']) || strlen($meta['artist']) == 0){
            if(strlen($meta['title']) == 0){
                $meta['title'] = $meta['song'];
            }
            $tmp = preg_split('/ - /',$meta['title'],2);
            if(count($tmp) > 1) {
                if(strlen(trim($tmp[1])) == 0){
                    $tmp[1] = $tmp[0];
                    $tmp[0] = '';
                    $songinfo['artist'] = $tmp[0];
                    $songinfo['title'] = $tmp[1];
                } else {
                    $songinfo['artist'] = $tmp[0];
                    $songinfo['title'] = $tmp[1];
                }
            } else {
                $songinfo['title'] = $tmp[0];
                $songinfo['artist'] = '';
            }
        } else {
            $songinfo['artist'] = $meta['artist'];
            $songinfo['title'] = $meta['title'];
        }
        $show = checkShow();
        if (($songinfo['show'] = $show['show']) != false) {
            $sql = "SELECT titleid, UNIX_TIMESTAMP(begin) as begin, UNIX_TIMESTAMP(NOW()) as end FROM songhistory WHERE end IS NULL";
            $dbres = $db->query($sql);
            if($dbres && $db->num_rows($dbres) > 0) {
                if($lastsong = $db->fetch($dbres)) {
                    $sql = "UPDATE songhistory SET end = NOW() WHERE end IS NULL;";
                    $db->execute($sql);
                    updateTitleLength($lastsong['titleid'], $lastsong['end'] - $lastsong['begin']);

                }
            }
            $sql = "INSERT INTO songhistory (`show`,begin,artist,title,titleid)
                    VALUES (".$songinfo['show'].",NOW(),
                           '".$db->escape($songinfo['artist'])."',
                           '".$db->escape($songinfo['title'])."',
                            ".$db->escape(getTitleId(getArtistId($songinfo['artist']), $songinfo['title'])).")";
            $db->execute($sql);
        }
    }else{
        checkShow();
    }
}

/**
 * returns streamer id
 * @return  integer
 */
function getStreamer(){
    global $db;
    $sql = "SELECT streamer FROM streamer WHERE status = 'STREAMING' LIMIT 1;";
    $result = $db->query($sql);
    if($db->num_rows($result) > 0){
        $id = $db->fetch($result);
        return $id['streamer'];
    }
    return false;
}
/**
 * returns Current (planned) Show,Type and streamerid
 */
function getCurrentShow() {
    global $db;
    $sql = "SELECT `show`, type, streamer
              FROM shows
             WHERE NOW() BETWEEN shows.begin AND shows.end;";
    $dbres = $db->query($sql);
    if($dbres && $db->num_rows($dbres) > 0) {
        if($show = $db->fetch($dbres)) {
            return $show;
        }
    }
    return false;
}

function getCurrentShowByStreamer($streamer) {
    global $db;
    $sql = "SELECT `show`, type, streamer
              FROM shows
             WHERE NOW() BETWEEN shows.begin AND shows.end
               AND streamer = ".$db->escape($streamer).";";
    $dbres = $db->query($sql);
    if($dbres && $db->num_rows($dbres) > 0) {
        if($show = $db->fetch($dbres)) {
            return $show;
        }
    }
    return false;
}
/**
 * returns currently streamed Show,Type and streamerid
 */
function getActiveShow() {
    global $db;
    $sql = "SELECT `show`, type, streamer
            FROM streamer
            JOIN shows USING ( streamer )
            WHERE streamer.status = 'STREAMING'
            AND (shows.end IS NULL
                 OR NOW() BETWEEN shows.begin AND shows.end)";
    $dbres = $db->query($sql);
    $show = array();
    if($dbres && $db->num_rows($dbres) > 0) {
        while( $show = $db->fetch($dbres) ){
            if($show['type'] == 'UNPLANNED') {
                break;
            }
        }
    }
    if(count($show) > 0){
        return $show;
    } else {
        return false;
    }
}

function addUnplannedShow() {
    global $db;
    $showname = '';
    $showdescription = '';
    $streamer = 0;
    $sql = "SELECT streamer
            FROM streamersettings
            JOIN streamer USING (streamer)
            WHERE status = 'STREAMING'
              AND `key` = 'icytags'";
    $dbres = $db->query($sql);
    if($dbres && $db->num_rows($dbres) > 0) {
        $s = $db->fetch($dbres);
        $streamer = (int)$s['streamer'];
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
        $sql = "SELECT `key`, value, streamer
                    FROM streamersettings
                    JOIN streamer USING (streamer)
                    WHERE status = 'STREAMING'
                      AND `key` IN ('defaultshowname', 'defaultshowdescription');";
        $res = $db->query($sql);
        while($row = $db->fetch($res)) {
            $streamer = (int)$row['streamer'];
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
    $db->execute($sql);
    $sql = "INSERT INTO shows (streamer, name, description, begin, type)
                SELECT streamer, '".$db->escape($showname)."', '".$db->escape($showdescription)."', NOW(), 'UNPLANNED'
                FROM streamer
                WHERE STATUS = 'STREAMING'";
    if( $db->execute($sql) ){
        return array('show' =>$db->insert_id(),'type' => 'UNPLANNED', 'streamer' => $streamer);
    }else {
        return false;
    }
    return false;
}

function checkShow() {
    global $db;
    $streamer = getStreamer();
    if($streamer) {
        $cshow = getCurrentShowByStreamer($streamer);
    } else {
        $cshow = false;
    }
    $ashow = getActiveShow();
    if ($cshow){ //some show is planned by current streamer
        recordStartHook($cshow['show']);
        return $cshow;
    } else if($ashow) { //some show is currently being streamed
        recordStopHook();
        if($ashow['streamer'] !== $streamer) {
            return addUnplannedShow(); //add a new, unplanned, show
        }
        return $ashow;
    } else if ($streamer) { //no show is currently streamed or planned
        return addUnplannedShow(); //add a new, unplanned, show
    }
    return false;
}

function recordStartHook($showid) {
    global $db, $_config;
    if($_config['record_auto'] == true) {
        $sql = "SELECT `show`, recording
                  FROM recordings
                 WHERE status = 'RECORDING'
                   AND flag = 2
                 ORDER BY recording ASC LIMIT 1;";
        $dbres = $db->query($sql);
        if($dbres) {
            if($db->num_rows($dbres) == 0) {
                    startRecording();
            } else if ($db->num_rows($dbres) == 1) {
                $rec = $db->fetch($dbres);
                if($rec['show'] != $showid) { //show has changed
                    stopRecording();
                }
            } else if ($db->num_rows($dbres) > 1){
                echo 'Database inconsistent! (multiple entries with status = RECORDING)'."\n";
            }
        } else {
            echo 'FIXME: SQL-Error in recordHook'."\n";
        }
    }
}

function recordStopHook() {
    global $db;
    $sql = "SELECT `show`, recording
                  FROM recordings
                 WHERE status = 'RECORDING'
                 ORDER BY recording ASC LIMIT 1;";
    $dbres = $db->query($sql);
    if($dbres) {
        if($db->num_rows($dbres) == 1) {
            stopRecording();
        }
    }
}



///////////////////////////////////////////////////////
//                  functions :3                     //
///////////////////////////////////////////////////////

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


function getArtistId($name) {
    global $db;
    $name = trim($name);
    $sql = 'SELECT artist FROM artists WHERE name = "'.$db->escape($name).'";';
    $dbres = $db->query($sql);
    if($dbres && $artist = $db->fetch($dbres)) {
        return $artist['artist'];
    } else {
        $sql = 'INSERT INTO artists (name) VALUES ("'.$db->escape($name).'")';
        if($db->execute($sql)) {
            return $db->insert_id();
        }
    }
}

function getTitleId($artist, $title) {
    global $db;
    $title = trim($title);
    $sql = 'SELECT title, length, lengthweight FROM titles WHERE name = "'.$db->escape($title).'" AND artist = '.$db->escape($artist).';';
    $dbres = $db->query($sql);
    if($dbres && $rtitle = $db->fetch($dbres)) {
        return $rtitle['title'];
    } else {
        $sql = 'INSERT INTO titles (artist,name)
                     VALUES ('.$db->escape($artist).',"'.$db->escape($title).'")';
        if($db->execute($sql)) {
            return $db->insert_id();
        }
    }
}

function updateTitleLength($titleid, $length){
    global $db;
    if($length <= 0)
        $length = 0;
    $sql = 'SELECT title, length, lengthweight FROM titles WHERE title = '.$db->escape($titleid).';';
    $dbres = $db->query($sql);
    if($dbres && ($rtitle = $db->fetch($dbres))) {
        if(!isset($rtitle['lengthweight']) || $rtitle['lengthweight'] < 0) {
            $rtitle['lengthweight'] = 0;
        }
        if(!isset($rtitle['length']) || $rtitle['length'] < 0) {
            $rtitle['length'] = 0;
        }
        $calclength = (($rtitle['length']*$rtitle['lengthweight'])+$length)/($rtitle['lengthweight']+1);
        $sql = 'UPDATE titles
                   SET length = '.$db->escape($calclength).',
                       lengthweight = '.$db->escape($rtitle['lengthweight']+1).'
                 WHERE title = '.$db->escape($titleid);
        $db->execute($sql);
    }
}
?>
