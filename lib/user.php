<?php

class User{
    var $username = false;
    var $userid = false;
    var $logged_in = false;
    var $rights = array();
    var $country = 'unknown';

    function User(){
        global $_config,$_MSG;
        $this->username = $_config['default-username'];
        if($this->logged_in()){
            if(isset($_GET['logout']) && $_GET['logout'] === 'true'){
                $this->logout();
                $_MSG['msg'][] = "Erfolgreich abgemeldet!";
            }
        }else if(isset($_POST['login']) && strlen($_POST['username']) != 0 && strlen($_POST['password']) != 0 ){
            $this->login($_POST['username'],$_POST['password']);
            if(!$this->logged_in){
                $_MSG['err'][] = "Benutzername und/oder Passwort falsch!";
            }else{
                $_MSG['msg'][] = "Erfolgreich angemeldet;";
            }
        } else {
            //not logged in
        }
        if($this->logged_in){
            //disabled for now
        }
        $this->setLocale();
    }
    /**
     * authenticates the user
     * @param $username
     * @param $password
     */
    function login($username, $password){
        global $db;
        $db->debugquery = false;
        $sql="SELECT streamer,username
              FROM streamer
    		  WHERE username='".$db->escape($username)."' AND password=SHA1('".$db->escape($password)."')
    		  LIMIT 1";
        $result= $db->query($sql);
        $location = getLocation($_SERVER['REMOTE_ADDR']);
        if ( $db->num_rows($result) == 1) {
            $user = $db->fetch($result);
            $this->userid = $user['streamer'];
            $this->username = $user['username'];
            $this->logged_in = true;
            $sql="UPDATE streamer
                     SET session='".session_id()."',
                         country='".$db->escape($location['cc'])."'
                     WHERE streamer='".$this->userid."'";
            $db->execute($sql);
        }else{
            $this->logged_in = false;
        }
        $db->debugquery = true;
    }

    /**
     * checks the user logged in
     */
    function logged_in()
    {
        global $db;
        $sql="SELECT streamer,username
		FROM streamer
		WHERE session='".session_id()."'
		LIMIT 1";
        $result = $db->query($sql);
        if($db->num_rows($result) == 1 ){
            $user = $db->fetch($result);
            $this->userid = $user['streamer'];
            $this->username = $user['username'];
            $this->logged_in = true;
            return true;
        }else{
            return false;
        }
    }

    /**
     * check if user is admin
     */
    function is_admin()
    {
        global $db;
        if($this->logged_in) {
            $sql = "SELECT value
                    FROM streamersettings
                    JOIN streamer USING (streamer)
                    WHERE `key` = 'admin'
                    AND value='true'
                    AND streamer = ".$this->userid;
            $dbres = $db->query($sql);
            if($row = $db->fetch($dbres)){
                if($row['value'] == 'true'){
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * logs the user out
     */
    function logout()
    {
        global $db,$_config;
        $sql="UPDATE streamer
              SET session = NULL
              WHERE session = '".session_id()."'";
        $db->execute($sql);
        $this->logged_in = false;
        $this->username = $_config['default-username'];
    }

    /**
     * returns true if the user is logged in
     * @return boolean
     */
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

    private function setLocale() {
        global $db,$lang;
        if(isset($_GET['locale']) && $_GET['locale'] > 0){
            $locale = (int)$_GET['locale'];
        }else if(isset($_COOKIE['rfk_locale'])){
            $locale = (int)$_COOKIE['rfk_locale'];
        }else if($location = getLocation($_SERVER['REMOTE_ADDR'])){
            if(strlen($location['cc']) > 0 ) {
                $sql = "SELECT * FROM locales WHERE country = '".$db->escape($location['cc'])."' LIMIT 1;";
                $dbres = $db->query($sql);
                if($row = $db->fetch($dbres)) {
                    $locale = $row['locale'];
                }else{
                    $locale = 3;
                }
            }else{
                    $locale = 3;
            }
        }

        if(!($locale > 0)){
            return;
        }
        $sql = "SELECT * FROM locales WHERE locale = ".$locale;
        $dbres = $db->query($sql);
        if($row = $db->fetch($dbres)) {
            $lang = new Lang($row['language']);
            $this->country = $row['country'];
            if(isset($_GET['locale'])) {
                //hurr durr 10 jahre
                setcookie('rfk_locale',$row['locale'],time()+60*60*24*365*10);
            }
        }
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
        if(preg_match('/^[A-Za-z0-9_-]+$/',$username)) {
            $sql = "INSERT INTO streamer (username,password)
                    VALUES ('".$db->escape($username)."',SHA('".$db->escape($password)."'))";
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
        $sql = "UPDATE streamer SET streampassword = '".$db->escape($streampassword)."' WHERE streamer = ".$this->userid." LIMIT 1;";
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