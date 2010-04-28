<?
/**
 * Global functions
 */

 function cleanup(&$template){
	global $_HEAD,$_MSG,$user,$Lang;
	foreach($user->rights as $right){
		$template->assign('RIGHT_'.$right,true);
	}
    $template->assign('USERNAME',$user->username);
    if($user->logged_in){
		$template->assign('user_log_in','true');
	}else{
		$template->assign('user_log_in','false');
	}
	if(count($_MSG['err']) > 0){
		$template->assign('MSG_ERR',$_MSG['err']);
	}
	if(count($_MSG['warn']) > 0){
		$template->assign('MSG_WARN',$_MSG['warn']);
	}
	if(count($_MSG['msg']) > 0){
		$template->assign('MSG_MSG',$_MSG['msg']);
	}
	if(isset($_HEAD['refresh'])){
		$template->assign('HEAD_REFRESH',$_HEAD['refresh']);
	}
}


?>