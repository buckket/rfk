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
}

?>