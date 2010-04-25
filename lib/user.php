<?php

class User{
	var $username = false;
	var $userid = false;
	var $logged_in = false;
	var $rights = array();
	function User(){
		global $_config,$_MSG;
        $this->username = $_config['default-username'];
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
			$this->get_userrights();
		}
	}
	function login($username, $password){
		global $db;
		$sql="SELECT id,name,lang
   			  FROM streamer
    		  WHERE username='".$db->escape($username)."' AND pass=SHA1('".$db->escape($password)."')
    		  LIMIT 1";
    	$result= $db->query($sql);
	    if ( $dbi->num_rows($result) == 1) {
        	$user = $dbi->fetch($result);
        	$this->userid = $user['id'];
        	$this->username = $user['username'];
			$this->logged_in = true;
			$this->lang = $user['lang'];
		    $sql="UPDATE streamer
    			  SET session='".session_id()."'
		    	  WHERE id=".$this->userid;
			$db->execute($sql);
    	}
    	else{
			$this->logged_in = false;
		}
	}
	function logged_in()
	{
		global $db;
		$sql="SELECT userid,username
		FROM streamer
		WHERE session='".session_id()."'
		LIMIT 1";
		$result = $db->query($sql);
		if($db->num_rows($result) == 1 ){
			$user = $db->fetch($result);
			$this->userid = $user['id'];
			$this->username = $user['username'];
			$this->logged_in = true;
			return true;
		}else{
			return false;
		}

	}
	function logout()
	{
		global $db;
		$sql="UPDATE streamer
		SET session=NULL
		WHERE session='".session_id()."'";
		$db->execute($sql);
		$this->logged_in = false;
	}
	function is_logged_in(){
		return $this->logged_in;
	}
    
	function get_userrights(){
		global $db;
        /**
		$sql = "SELECT `right` from streamerrights Where user_id = ".$this->userid.";";
		$result = $db->query($sql);
		while($row = $db->fetch($result)){
			$this->rights[] = $row['right'];
		}
        **/
	}
	
	function has_right($right){
		return in_array($right,$this->rights);	
	}
    /**
     *   TODO spamfilter
     *  returncode  desc
     *   0          ok
     *  -1          Username contains "|"
     *  -2          SQL-Error
     */
	function register($username,$password){
        global $db;
        if(strpos($username,'|') === false){
            $sql = "INSERT INTO streamer (registertime,username,password,name,defaultshowname)
                    VALUES (NOW(),'".$db->escape($username)."',SHA('".$db->escape($password)."'),'".$db->escape($username)."','".$db->escape($username)."')";
            if($db->execute($sql)){
                $this->userid = $db->insert_id();
                return 0;
            }else{
                return -2;
            }
        }else{
            return -1;
        }
    }
    
    function set_streampassword($streampassword){
        global $db;
        $sql = "UPDATE streamer SET streampassword = '".$db->escape($streampassword)."' WHERE userid = ".$this->userid." LIMIT 1;";
        return $db->execute($sql);
    }
    
    function set_djname($djname){
        //TODO stub
    }
    
    function set_showname($showname){
        //TODO stub
    }
}

?>