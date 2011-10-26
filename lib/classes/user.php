<?php
/**
 * Userclass
 * @author teddydestodes
 */
class User{
    /**
     * Username
     * @var string
     */
    private $username = false;
    /**
     * userId
     * @var integer
     */
    private $userid = false;
    /**
     * @var boolean
     */
    private $logged_in = false;

    private $country = 'unknown';

    public function __construct(){}

    public static $USERNAME_VALID = 0;
    public static $USERNAME_TAKEN = 1;
    public static $USERNAME_VIOLATES_RULES = 2;
    public static $USERNAME_INVALID_LENGTH = 4;

    /**
     * authenticates the user
     * @param $username
     * @param $password
     */
    public function login($username, $password){
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
     * @return boolean
     */
    function isLoggedIn($recheck = false)
    {
        if(!isset($this->logged_in) || $recheck) {
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
            }else{
                $this->logged_in = false;
            }
        }
        return $this->logged_in;
    }

    /**
     * check if user is admin
     */
    function isAdmin()
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

    public function setLocale() {
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
                    $locale = 0;
                }
            }else{
                    $locale = 0;
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

    public function getUserId() {
        return $this->userid;
    }

    public function getUsername() {
        return $this->username;
    }

    public static function checkUsername($username) {
        if(preg_match('/^[A-Za-z0-9_-]+$/',$username)) {
            if(strlen($username) > 20 || strlen($username) < 3) {

            } else {
                return User::$USERNAME_INVALID_LENGTH;
            }
            global $db;
            $sql = 'SELECT streamer FROM streamers WHERE username = "'.$db->escape($username).'" LIMIT 1;';
            $dbres = $db->query($sql);
            if ($db->num_rows($dbres) > 0) {
                $db->free($dbres);
                return User::$USERNAME_TAKEN;
            } else {
                return User::$USERNAME_VALID;
            }
        } else {
            return User::$USERNAME_VIOLATES_RULES;
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
}

?>