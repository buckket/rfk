<?php
$flags = array('disabled'     => 1,
               'viewip'       => 2,
               'less5seconds' => 4,
               'kickallowed'  => 8);
$basePath = dirname(dirname(dirname(__FILE__)));
require_once $basePath.'/lib/common.inc.php';
require_once $basePath.'/lib/liquidsoaptelnet.php';
require_once $basePath.'/lib/api.inc.php';

function throw_error($id, $error){
    echo json_encode(array('errid'=> $id, 'error' => $error));
    exit;
}

if(isset($_GET['apikey']) && strlen($_GET['apikey']) > 5) {
    $sql = "SELECT apikey,`key`, flag, UNIX_TIMESTAMP(lastaccessed) as lastaccessed FROM apikeys WHERE `key` = '".$db->escape($_GET['apikey'])."' LIMIT 1;";
    $dbres = $db->query($sql);
    if ($dbres) {
        if($row = $db->fetch($dbres)) {
            //check if enabled
            if($row['flag']%$flags['disabled'] != 0) {
                throw_error(2, 'apikey has been disabled');
            }
            //quota :3
            if(!($row['flag']&$flags['less5seconds'])) {
                if(time()-$row['lastaccessed'] < 5 ) {
                    throw_error(3, 'you are querying to fast');
                }
            }
            $sql = 'UPDATE apikeys SET lastaccessed = NOW(), counter= counter+1 WHERE apikey = '.$row['apikey'].' LIMIT 1;';
            $db->execute($sql);
            handle_request($row['flag']);
        } else {
            throw_error(1, 'invalid apikey');
        }
    } else {
        throw_error(1337, 'internal error');
    }
} else {
    throw_error(0, 'no apikey given');
}

/**
 * errorlist
 * 1 - 19 reserved
 * 20   => cant kickdj
 * 1337 => sql-error
 */
function handle_request($flag) {
    // just for now :3
    global $flags;
    $out = array();
    if(isset($_GET['w'])&&strlen($_GET['w']) > 0) {
        $qrys = explode(',',$_GET['w']);
        foreach ($qrys as $qry) {
            switch($qry) {
                case 'dj':
                    getDJ($out);
                    break;
                case 'listener':
                    getListener($out);
                    break;
                case 'kickdj':
                    if ($flag&$flags['kickallowed']) {
                        kickDJ($out);
                    } else {
                        throw_error(20, 'sorry you can\'t do that');
                    }
                    break;
                case 'listenerdata':
                    if ($flag&$flags['viewip']) {
                        getListenerData($out);
                    } else {
                        throw_error(20, 'sorry you can\'t do that');
                    }
                    break;
                case 'track':
                    getCurrTrack($out);
                    break;
                case 'show':
                    getCurrShow($out);
                    break;
                case 'nextshows':
                    getNextShows($out);
                    break;
                case 'tracks':
                    getTracks($out);
                    break;
                case 'djid':
                    getDJID($out);
                    break;
                case 'traffic':
                    getTraffic($out);
                    break;
                case 'countries':
                    getCountries($out);
                    break;
                case 'auth':
                    authTest($out);
                    break;
                case 'authadd':
                    authAdd($out);
                    break;
                case 'authjoin':
                    authJoin($out);
                    break;
                case 'authpart':
                    authPart($out);
                    break;
                case 'authupdate':
                    authUpdate($out);
                    break;
                case 'isirc':
                    isIRC($out);
                    break;
                default:
                    $out['warning'][] = $qry.' does not exsist';
            }
        }
    }else {
        trigger_error(3,'no query');
    }
    echo json_encode($out);
    exit;
}

?>