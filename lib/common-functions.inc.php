<?
/**
 * Global functions
 */

function cleanup_h2o(&$template){
	global $_MSG,$user,$lang;
	$template['user_logged_in'] = $user->logged_in;
	$template['username'] = $user->username;

    if($user->logged_in ){
        $template['user_self'] = true;
    }else{
        $template['user_self'] = false;
    }
	$template['messages'] = array();
	if(count($_MSG['err']) > 0){
		$template['messages']['error'] = $_MSG['err'];
	}
	if(count($_MSG['warn']) > 0){
		$template['messages']['warn'] = $_MSG['warn'];
	}
	if(count($_MSG['msg']) > 0){
		$template['messages']['info'] = $_MSG['msg'];
	}
	$template['lang'] = $lang->getLang();
	$template['locales'] = $lang->getAvailLangs();
	$template['usercountry'] = $user->country;
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
    $str = file_get_contents('/tmp/vnstat');
    $out = array();
    if (preg_match('/tx.*([0-9])\\.([0-9]).*/', $str,$matches)) {
        $out['out'] = $matches[1].'.'.$matches[2];
    }
    if (preg_match('/rx.*([0-9])\\.([0-9]).*/', $str,$matches)) {
        $out['in'] = $matches[1].'.'.$matches[2];
    }
    $out['sum'] = $out['in']+$out['out'];

    return $out;
}
?>