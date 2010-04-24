<?php

class User{
	var $username = false;
	var $userid = false;
	var $logged_in = false;
	var $group = false;
	var $groupsettings = array();
	var $rights = array();
	var $lang;
	function User(){
		global $_MSG, $Lang,$_config;
		$this->lang = $_config['default_lang'];
		if($this->logged_in()){
			if($_GET['logout'] === 'true'){
				$this->logout();
				$_MSG['msg'][] = "Erfolgreich Ausgeloggt!";
			}					
		}else if(isset($_POST['login']) && strlen($_POST['username']) != 0 && strlen($_POST['password']) != 0 ){
			$this->login($_POST['username'],$_POST['password']);
			if(!$this->logged_in){
				$_MSG['err'][] = "Benutzername und/oder Passwort falsch!";
			}else{
				$Lang = new Lang($this->lang);
				$_MSG['msg'][] = $Lang->text('Login_successful');
			}
		}
		if($this->logged_in){
			$Lang = new Lang($this->lang);
			$this->get_userrights();
			$this->get_groupsettings();
		}
	}
	function login($username, $password){
		global $dbi;
		$sql="SELECT id,name,lang
   			  FROM jlog_admin_users
    		  WHERE name='".$dbi->escape($username)."' AND pass=MD5('".$dbi->escape($password)."')
    		  LIMIT 1";
    	$result= $dbi->query($sql);
	    if ( $dbi->num_rows($result) == 1) {
        	$user = $dbi->fetch($result);
        	$this->userid = $user['id'];
        	$this->username = $user['name'];
			$this->logged_in = true;
			$this->lang = $user['lang'];
		    $sql="UPDATE jlog_admin_users
    			  SET session='".session_id()."'
		    	  WHERE id=".$this->userid;
			$dbi->execute($sql);
    	}
    	else{
			$this->logged_in = false;
		}
	}
	function logged_in()
	{
		global $dbi;
		$sql="SELECT id,name,lang
		FROM jlog_admin_users
		WHERE session='".session_id()."'
		LIMIT 1";
		$result = $dbi->query($sql);
		if($dbi->num_rows($result) == 1 ){
			$user = $dbi->fetch($result);
			$this->userid = $user['id'];
			$this->username = $user['name'];
			$this->lang = $user['lang'];
			$this->logged_in = true;
			return true;
		}else{
			return false;
		}

	}
	function logout()
	{
		global $dbi;
		$sql="UPDATE jlog_admin_users
		SET session=NULL
		WHERE session='".session_id()."'";
		$dbi->execute($sql);
		$this->logged_in = false;
	}
	function is_logged_in(){
		return $this->logged_in;
	}
	
	function get_group(){
		if($this->group === false){
			global $dbi;
			$sql="SELECT u.group
			FROM jlog_admin_users as u
			WHERE u.session='".session_id()."'
			LIMIT 1";
			$result = $dbi->query($sql);
			if($result){
				$row = $dbi->fetch($result);
				$this->group = $row['group'];
			}else{
				return false;	
			}
		}
		return $this->group;
	}
	function get_groupsettings(){
		global $dbi;
		$this->get_group();
		$sql = "SELECT * from jlog_admin_group_settings Where groupid = ".$this->group.";";
		$result = $dbi->query($sql);
		while($row = $dbi->fetch($result)){
			if($row['key'] != 'ip'){
				$this->groupsettings[$row['key']] = $row['value'];
			}else{
				$this->groupsettings['ip'][] = $row['value'];
			}
		}
	}
	function get_userrights(){
		global $dbi;
		$sql = "SELECT `right` from jlog_admin_user_rights Where user_id = ".$this->userid.";";
		$result = $dbi->query($sql);
		while($row = $dbi->fetch($result)){
			$this->rights[] = $row['right'];
		}
	}
	
	function has_right($right){
		return in_array($right,$this->rights);	
	}
	
}

?>