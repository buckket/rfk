<?
/**
 * Global functions
 */

/**
 * checks if a user wants to login
 */
function isLogin() {
    return (isset($_POST['login']) && isset($_POST['username']) && isset($_POST['password']));
}

/**
 * checks if a user wants to logout
 */
function isLogout() {
    return (isset($_POST['logout']));
}

function create_api_key(){
    global $db;
    $watchdog = 0;
    do {
        $key = sha1(sha1(time().''.rand(1, 1000000).'apikey'));
        $dbres = $db->query("SELECT * FROM apikeys WHERE `key` = '".$db->escape($key)."' LIMIT 1;");
        $watchdog++;
        if($watchdog > 150) {
            return false;
        }
    } while ( $db->num_rows($dbres) > 0);
    return $key;
}

function checkCB($country){
    global $includepath;
    if(file_exists($includepath.'/../www/images/cb/'.strtolower($country).'.png'))
    return strtolower($country);
    return 'unknown';
}

function getTraffic(){
    $str = file_get_contents('../var/vnstat');
    $out = array();
    if (preg_match('/tx.*?([0-9]+)\\.([0-9]+).*/', $str,$matches)) {
        $out['out'] = $matches[1].'.'.$matches[2];
    }
    if (preg_match('/rx.*?([0-9]+)\\.([0-9]+).*/', $str,$matches)) {
        $out['in'] = $matches[1].'.'.$matches[2];
    }
    $out['sum'] = $out['in']+$out['out'];

    return $out;
}

function setIRCCount($count) {
    file_put_contents('../var/irccount', $count);
}

function getIRCCount() {
    return (int)file_get_contents('../var/irccount');
}

?>
