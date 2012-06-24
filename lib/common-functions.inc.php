<?php
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
	
	if($_SERVER["SERVER_NAME"] == "radio.krautchan.net") {
	    $template['style'] = "kc";
	}
	else if($_SERVER["SERVER_NAME"] == "radio.ernstchan.com") {
	    $template['style'] = "ec";
	}
	else
	{
	    $template['style'] = "kc";
	}
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
    global $db;
    
    $sql = "SELECT relay, tx FROM relays;";
    $dbres = $db->query($sql);
    $out = array();
    if($dbres) {
        while($row = $db->fetch($dbres)) {
            $out[] = array('relay' => $row['relay'],
            'out' => $row['tx']);
        }
    }
    return $out;
}

function redirect_to_page($page,$phpself) {
    $host = $_SERVER['HTTP_HOST'];
    $uri  = rtrim(dirname($phpself), '/\\');
    header("Location: http://$host$uri/$page");
}

function setIRCCount($count) {
    file_put_contents('../var/irccount', $count);
}

function getIRCCount() {
    return (int)file_get_contents('../var/irccount');
}

?>
