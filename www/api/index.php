<?php
$flags = array('disabled'     => 1,
               'viewip'       => 2,
               'less5seconds' => 4,
               'kickallowed'  => 8);
$basePath = dirname(dirname(dirname(__FILE__)));
require_once $basePath.'/lib/common.inc.php';
require_once $basePath.'/lib/liquidsoaptelnet.php';

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
function handle_request($flag) {
    // just for now :3
    global $flags;
    include_once dirname(__FILE__).'/radioquery.php';
}
?>