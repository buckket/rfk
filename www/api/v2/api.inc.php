<?php

$basePath = dirname(dirname(dirname(dirname(__FILE__))));
require_once $basePath.'/lib/common.inc.php';
/**
 * APIclass
 *
 * Errorhandling:
 *
 * Errorcode    Description
 * -----------------------------
 * 1			Error while parsing querys
 * 2			No Apikey
 * 3			Key disabled/banned
 * 4			Quota exceeded
 * 5			Invalid Key
 *
 * 8		 	Unknown apicall
 * 9			Error in apicall
 *
 * 128			putDJ error
 * 129          isIRC error
 * 130          setIRCCount error
 * 131          authTest error
 * 132          authAdd error
 * 133          authUpdate error
 * 134          authJoin error
 * 135          authPart error
 * 136          rConfig error
 * 137          rThread error
 *
 * 403			Forbidden
 * 500			Internal Error
 *
 * @author teddydestodes, MrLoom
 *
 */
class Api {

    var $querys = array();

    var $users = array();
    var $tracks = array();
    var $shows = array();

    var $output = array();
    var $error = array();

    var $flags = 0;
    var $key = null;

    /**
     * flags
     */
    const disabled  = 1;
    const viewip    = 2;
    const fastquery = 4;
    const kick      = 8;
    const ban      = 16;
    const auth     = 32;

    /**
     * This array is used to get the corresponding Function to an api call
     *
     * @var array
    */
    var $queryhooks = array('dj'          => 'putDj',
                            'currdj'      => 'putCurrDj',
                            'nextshows'   => 'putNextShows',
                            'currentshow' => 'putCurrShow',
                            'lastshows'   => 'putLastShows',
                            'currtrack'   => 'putCurrTrack',
                            'lasttracks'  => 'putLastTracks',
                            'listener'    => 'putListener',
                            'countries'   => 'putCountries',
                            'kick'        => 'kickDJ',
                            'isirc'       => 'isIRC',
                            'areirc'      => 'areIRC',
                            'setirccount' => 'setIRCCount',
                            'getirccount' => 'getIRCCount',
                            'authtest'    => 'authTest',
                            'authadd'     => 'authAdd',
                            'authupdate'  => 'authUpdate',
                            'authjoin'    => 'authJoin',
                            'authpart'    => 'authPart',
                            'rconf'       => 'rConfig',
                            'rthread'     => 'rThread');

    /**
     * Requeststatus
     *
     * 0 => ok
     * 1 => error and quit processing
     * @var unknown_type
    */
 var $state = 0;

    /**
     * Constructor
     *
     * @param array $query
    */
    public function __construct() {
        $this->parseGET();
        $this->checkKey();
        foreach($this->querys as $query => $args) {
            if($this->state > 0){
                break;
            }
            $this->doQuery($query,$args);
        }
    }

    private function doQuery($name,$args){
        if(isset($this->queryhooks[$name]) && strlen($this->queryhooks[$name]) > 0){
            try{
                $class = $this->queryhooks[$name];
                $this->$class($args);
            }catch(Exception $e){
                $this->putError(9, 'error in apicall \''.$name.'\'');
            }
        }else{
            $this->putError(8, 'unknown apicall \''.$name.'\'');
        }
    }

    private function putUser($id, $name){
        $this->users[(int)$id] = array('name' => $name);
    }

    private function putTrack($id,$artist, $title){
        $this->tracks[(int)$id] = array('artist' => $artist,
                                        'title'  => $title);
    }

    private function putShow($id, $name,$description,$type,$dj,$thread,$begin,$end){
        $this->shows[(int)$id] = array( 'name' => $name,
                                        'description' => $description,
                                        'begin'  => (int)$begin,
                                        'end'    => $end==0?null:(int)$end,
                                        'type'   => $type,
                                        'dj'     => (int)$dj,
                                        'thread' => $thread==0?null:(int)$thread);
    }

    private function putError($code, $message){
        $this->state = 1;
        $this->error = array('code' => $code, 'message' => $message);
    }

    /**
     * Check Key and Quota
    */
    private function checkKey(){
        global $db;
        if(!(isset($this->key)) || strlen($this->key)< 10) {
            $this->putError(2, 'No Apikey given!');
            return ;
        }
        $sql = "SELECT apikey,`key`, flag, UNIX_TIMESTAMP(lastaccessed) as lastaccessed
                FROM apikeys
                WHERE `key` = '".$db->escape($this->key)."'
                LIMIT 1;";
        $dbres = $db->query($sql);
        if ($dbres) {
            if($row = $db->fetch($dbres)) {
                //check if enabled
                $this->flags = (int)$row['flag'];
                if($this->flags % self::disabled != 0) {
                    $this->putError(3, 'This Apikey is disabled');
                    return;
                }
                //quota :3
                if(!($this->flags&self::fastquery)) {
                    if(time()-$row['lastaccessed'] < 5 ) {
                        $this->putError(4, 'You\'ve exceeded your quota');
                    }
                }
                $sql = 'UPDATE apikeys SET lastaccessed = NOW(), counter= counter+1 WHERE apikey = '.$row['apikey'].' LIMIT 1;';
                $db->execute($sql);
            } else {
                $this->putError(5, 'This Apikey is invalid!');
            }
        } else {
            $this->putError(500, 'Internal Error');
        }
    }

    /**
     * parses the Request
    */
    private function parseGET(){
        foreach($_GET as $name => $query){
            $qry = array();
            if(strlen($query)>0) {
                $qtmp = explode(':', $query);

                if($name == 'key') {
                    if(count($qtmp) == 1) {
                        $this->key = $qtmp[0];
                    } else {
                        $this->putError(2, 'No Apikey given!');
                        return ;
                    }
                    continue;
                }
                if (count($qtmp)%2 == 0) {
                    for($i = 0; $i < count($qtmp) ;$i += 2){
                        if(strlen($qtmp[$i+1]) > 0){
                            $qry[$qtmp[$i]] = $qtmp[$i+1];
                        } else {
                            $qry[$qtmp[$i]] = true;
                        }
                    }
                } else {
                    $this->putError(1, 'Wrong argument/value-count (e.g. argument without value)');
                }
            }
            $this->querys[$name] = $qry;
        }
    }

    /**
     * returns a jsonecoded hash
     *
     * @return string
    */
    public function getJson(){
        if($this->state > 0){
            return json_encode(array('state' => $this->state, 'error' => $this->error));
        }else{
            $out = $this->output;
            if(count($this->shows) > 0){
                $out['shows'] = $this->shows;
            }
            if(count($this->users) > 0){
                $out['users'] = $this->users;
            }
            if(count($this->tracks) > 0){
                $out['tracks'] = $this->tracks;
            }
            return json_encode($out);
        }
    }

    //-- apifunction belong below this!!!

    private function putLastShows($args){
        global $db;
        if(isset($args['count']) && $args['count'] > 1){
            $limit = $args['count'];
        }else{
            $limit = 1;
        }
        $sql  = 'SELECT `show`, thread,UNIX_TIMESTAMP(begin) as b,UNIX_TIMESTAMP(end) as e, name, description, type, username, streamer
                FROM shows
                JOIN streamer USING (streamer)
                WHERE end < NOW() ';

        if(isset($args['dj']) && strlen($args['dj']) > 0) {
            $sql .= 'AND username = "' . $db->escape($args['dj']) . '" ';
        }

        $sql .= 'ORDER BY end DESC
                    LIMIT 0,'.$limit;

        $dbres = $db->query($sql);
        $this->output['lastshows'] = array();
        if($dbres) {
            while($row = $db->fetch($dbres)) {
                $this->putUser($row['streamer'], $row['username']);
                $this->putShow($row['show'], $row['name'], $row['description'], $row['type'], $row['streamer'], $row['thread'], $row['b'], $row['e']);
                $this->output['lastshows'][] = (int)$row['show'];
            }
        }
    }

    function putNextShows($args){
        global $db;
        if(isset($args['count']) && $args['count'] > 1){
            $limit = $args['count'];
        }else{
            $limit = 1;
        }
        $sql  = 'SELECT `show`, thread,UNIX_TIMESTAMP(begin) as b,UNIX_TIMESTAMP(end) as e, name, description, type, username, streamer
                FROM shows
                JOIN streamer USING (streamer)
                WHERE begin > NOW() ';

        if(isset($args['dj']) && strlen($args['dj']) > 0) {
            $sql .= 'AND username = "' . $db->escape($args['dj']) . '" ';
        }

        $sql .= 'ORDER BY begin ASC
                    LIMIT 0,'.$limit;

        $dbres = $db->query($sql);
        $this->output['nextshows'] = array();
        if($dbres) {
            while($row = $db->fetch($dbres)) {
                $this->putUser($row['streamer'], $row['username']);
                $this->putShow($row['show'], $row['name'], $row['description'], $row['type'], $row['streamer'], $row['thread'], $row['b'], $row['e']);
                $this->output['nextshows'][] = (int)$row['show'];
            }
        }
    }

    private function putCurrShow($args){
        global $db;
        $sql = 'SELECT `show`, UNIX_TIMESTAMP(begin) as b,UNIX_TIMESTAMP(end) as e,name, description,type, username, streamer, status, thread
                FROM shows
                JOIN streamer USING (streamer)
                WHERE end IS NULL
                OR NOW() between begin AND end;';
        $dbres = $db->query($sql);

        $this->output['currentshow']['id'] = null;
        $this->output['currentshow']['streaming'] = false;
        if($dbres && $db->num_rows($dbres) > 0) {
            while($row = $db->fetch($dbres)) {
                $this->putShow($row['show'], $row['name'], $row['description'], $row['type'], $row['streamer'], $row['thread'], $row['b'], $row['e']);
                $this->putUser($row['streamer'], $row['username']);
                if($row['type'] == 'UNPLANNED') {
                    $this->output['currentshow']['id'] = (int)$row['show'];
                }
                if($row['type'] == 'PLANNED') {
                    if($db->num_rows($dbres) == 1) {
                        $this->output['currentshow']['id'] = (int)$row['show'];
                    } else {
                        $this->output['currentshow']['planned'] = (int)$row['show'];
                    }
                }
                if($row['status'] == 'STREAMING'){
                    $this->output['currentshow']['streaming'] = true;
                }
            }
        }
    }

    private function putDJ($args){
        global $db;

        if(isset($args['name'])) {
            $sql = "SELECT * FROM streamer WHERE username = '" . $db->escape($args['name']) . "' LIMIT 1;";
        }else if(isset($args['id'])) {
            $sql = "SELECT * FROM streamer WHERE streamer = '" . $db->escape($args['id']) . "' LIMIT 1;";
        }else{
            $this->putError(128, "'dj' needs at least one argument [name|id]!");
            return;
        }
        $dbres = $db->query($sql);
        if($dbres) {
            $row = $db->fetch($dbres);
            $this->putUser($row['streamer'],$row['username']);
            $this->output['dj'] = $row['streamer'];
        }
    }

    private function putCurrDJ($args){
        global $db;
        $sql = "SELECT * FROM streamer WHERE status='STREAMING' LIMIT 1;";
        $dbres = $db->query($sql);
        $this->output['currdj'] = null;
        if($dbres) {
            $row = $db->fetch($dbres);
            $this->putUser($row['streamer'],$row['username']);
            $this->output['currdj'] = $row['streamer'];
        }
    }

    private function putCurrTrack($args) {
        global $db;
        $lasttrack = 0;
        if(isset($args['lasttrack']) && $args['lasttrack'] > 0){
            $lasttrack = $db->escape($args['lasttrack']);
        }
        $sql = "SELECT *
                FROM songhistory
                WHERE end IS NULL
                AND song > ".$lasttrack.";";
        $dbres = $db->query($sql);
        $this->output['currtrack'] = null;
        if($dbres) {
            if($row = $db->fetch($dbres)) {
                $this->putTrack($row['song'], $row['artist'], $row['title']);
                $this->output['currtrack'] = (int)$row['song'];
            }
        }
    }

    private function putLastTracks($args){
        global $db;
        if(isset($args['count']) && $args['count'] > 1){
            $limit = $args['count'];
        }else{
            $limit = 1;
        }
        $sql = 'SELECT `song`,title, artist
                FROM songhistory
                ORDER BY song DESC
                LIMIT 0,'.$limit.';';
        $dbres = $db->query($sql);
        $tmp = array();
        if($dbres) {
            while($row = $db->fetch($dbres)) {
                $this->putTrack($row['song'], $row['artist'], $row['title']);
                $this->output['lasttracks'][] = (int)$row['song'];
            }
        }
    }

    private function putListener($args){
        global $db;
        $sql = "SELECT name, IF(c IS NULL, 0, c) as c, description
                FROM (SELECT COUNT(*) as c, mount
                      FROM listenerhistory
                      WHERE disconnected IS NULL
                      GROUP BY mount) as c
                RIGHT JOIN mounts USING (mount);";
        $dbres = $db->query($sql);
        $tmp = array();
        if($dbres) {
            while($row = $db->fetch($dbres)) {
                $this->output['listener'][$row['name']] = array('description' => $row['description'],
                                                                'count'       => (int)$row['c']);
            }
        }
    }

    private function putCountries($args) {
        global $db;

        $sql =  'SELECT c, country
                FROM (SELECT COUNT(*) as c, country
                FROM listenerhistory
                WHERE disconnected IS NULL
                GROUP BY country) as c
                ORDER BY c DESC;';

        $dbres = $db->query($sql);
        $tmp = array();
        if($dbres) {
            while($row = $db->fetch($dbres)) {
                $this->output['countries'][] = array('country' => $row['country'],
                                                     'count' => (int)$row['c']);
            }
        }
    }


    /**
     * kicks a DJ
     * @param array $args
    */
    private function kickDJ($args){
        if($this->flags&self::kick != 0){
            global $basePath;
            require_once $basePath.'/lib/liquidsoaptelnet.php';
            $liquid = new Liquidsoap;
            $liquid->connect();
            $liquid->getHarborSource();
            $liquid->kickHarbor();

            global $db;
            $timestamp = time() + (2 * 60);
            $timestamp = date('Y-m-d H:i:s', $timestamp);
            if(isset($args['dj']) && $args['dj'] > 0) {
                $sql = "UPDATE streamer SET ban = '". $timestamp . "' WHERE streamer = '". $args['dj'] ."';";
            } else {
                $sql = "UPDATE streamer SET ban = '". $timestamp . "' WHERE status = 'STREAMING';";
            }
            if(!($dbres = $db->execute($sql))){
                $this->putError(500, 'SQL error');
            }
        } else {
            $this->putError(403, 'You dont have kick permission');
        }
    }

    /**
     * bans a DJ by id
     * @todo alles
     * @param array $args
    */
    private function banDJ($args){
        if($this->flags&self::ban != 0){
            global $db;
            $timestamp = time() + (2 * 60);
            $timestamp = date('Y-m-d H:i:s', $timestamp);
            $sql = "UPDATE streamer SET ban = '". $timestamp . "' WHERE streamer = '". $args['dj'] ."';";
            $dbres = $db->query($sql);
            $out['status'] = 0;
        } else {
            $this->putError(403, 'You dont have ban permission');
        }
    }

    private function getTraffic($args) {
        //didn't want to include common-functions.inc.php
        global $basePath;
        $str = file_get_contents($basePath.'/var/vnstat');
        $tmp = array();
        if (preg_match('/tx.*?([0-9]+)\\.([0-9]+).*/', $str,$matches)) {
            $tmp['out'] = $matches[1].'.'.$matches[2];
        }
        if (preg_match('/rx.*?([0-9]+)\\.([0-9]+).*/', $str,$matches)) {
            $tmp['in'] = $matches[1].'.'.$matches[2];
        }
        $tmp['sum'] = $tmp['in']+$tmp['out'];
        $this->output['traffic'] = $tmp;
    }

    private function getHostmask($id) {
        global $db;
        if(isset($id)) {
            $sql = "SELECT * FROM streamersettings JOIN streamer using(streamer) WHERE `key` = 'hostmask' AND streamer = '" . $db->escape($id) . "';";
            $dbres = $db->query($sql);
            if($dbres && $db->num_rows($dbres) > 0) {
                if($row = $db->fetch($dbres)) {
                    return $row['value'];
                }
            }
        }
    }

    private function isIRC($args) {
        if(!($this->flags&self::auth)){
            $this->putError(403, 'You dont have auth permission');
            return;
        }

        global $db;
        if(isset($args['id']) && strlen($args['id']) > 0) {
            $sql = "SELECT * FROM streamersettings
                    JOIN streamer using(streamer)
                    WHERE `key` = 'isIRC'
                    AND value = 1
                    AND streamer = '" . $db->escape($args['id']) . "';";
            $dbres = $db->query($sql);
            if($dbres && $db->num_rows($dbres) > 0) {
                if($row = $db->fetch($dbres)) {
                    $this->output['isirc']['hostmask'] = $this->getHostmask($row['streamer']);
                    $this->output['isirc']['nick'] = $row['username'];
                    $this->output['isirc']['id'] = $row['streamer'];
                    $this->output['isirc']['status'] = 0;
                    return;
                }
            }
        }
        else {
            $this->putError(129, "'isirc' needs at least one argument [id]!");
            return;
        }
        $this->output['isirc']['status'] = 1;
    }

    private function areIRC($args) {
        if(!($this->flags&self::auth)){
            $this->putError(403, 'You dont have auth permission');
            return;
        }

        global $db;
        $sql = "SELECT * FROM streamersettings
                JOIN (SELECT streamer FROM streamersettings
                WHERE `key` = 'isIRC'
                AND value = 1) as a USING(streamer)
                WHERE `key` = 'hostmask';";
        $dbres = $db->query($sql);
        $tmp = array();
        if($dbres) {
            while($row = $db->fetch($dbres)) {
                $tmp[] = $row['value'];
            }
        $this->output['areirc'] = $tmp;
        }
    }

    private function setIRCCount($args) {
        if(!($this->flags&self::auth)){
            $this->putError(403, 'You dont have auth permission');
            return;
        }

        global $basePath;
        if(isset($args['c']) && (is_int((int)$args['c']))) {
            if(file_put_contents($basePath.'/var/irccount', (int)$args['c'])) {
                $this->output['setirccount']['status'] = 0;
            }
            else {
                $this->output['setirccount']['status'] = 1;
            }
        }
        else {
            $this->putError(130, "'setIRCCount' needs at least one argument [c]!");
            return;
        }
    }

    private function getIRCCount($args) {
        global $basePath;
        $this->output['getirccount']['c'] = (int)file_get_contents($basePath.'/var/irccount');
    }

    private function authTest($args) {
        if(!($this->flags&self::auth)){
            $this->putError(403, 'You dont have auth permission');
            return;
        }

        global $db;
        if(isset($args['hostmask']) && strlen($args['hostmask']) > 0) {
            $hostmask = explode('!', $args['hostmask']);
            $sql = "SELECT streamer, username
                    FROM streamersettings
                    JOIN streamer using(streamer)
                    WHERE `key` = 'hostmask'
                    AND `value` REGEXP '[A-z0-9]+!". $db->escape($hostmask[1]) . "' ;";
            $dbres = $db->query($sql);
            if($dbres) {
                if($row = $db->fetch($dbres)) {
                    $this->output['auth']['nick'] = $row['username'];
                    $this->output['auth']['id'] = $row['streamer'];
                    $this->output['auth']['status'] = 0;
                    return;
                }
            }
        }
        else {
            $this->putError(131, "'authTest' needs at least one argument [hostmask]!");
            return;
        }
        $this->output['auth']['status'] = 1;
    }

    private function authAdd($args) {
        if(!($this->flags&self::auth)){
            $this->putError(403, 'You dont have auth permission');
            return;
        }

        global $db;
        if((isset($args['hostmask']) && strlen($args['hostmask']) > 0) && (isset($args['user']) && strlen($args['user']) > 0) && (isset($args['pass']) && strlen($args['pass']) > 0)) {
            $sql = "SELECT * FROM streamer
                    WHERE username = '" . $db->escape($args['user']) . "'
                    AND streampassword = '" . $db->escape($args['pass']) . "';";
            $dbres = $db->query($sql);
            if($dbres && $db->num_rows($dbres) > 0) {
                if($row = $db->fetch($dbres)) {
                    $sql = "INSERT INTO streamersettings (streamer,`key`,value)
                            VALUES (" . $row['streamer'] . ",'hostmask','" . $db->escape($args['hostmask']) ."')
                            ON DUPLICATE KEY UPDATE value = '" . $db->escape($args['hostmask']) . "';";
                    if($db->execute($sql)) {
                        $this->output['auth']['nick'] = $row['username'];
                        $this->output['auth']['id'] = $row['streamer'];
                        $this->output['auth']['status'] = 0;
                        return;
                    }
                }
            }
        }
        else {
            $this->putError(132, "'authAdd' needs three arguments [hostmask, user, pass]!");
        }
        $this->output['auth']['status'] = 1;
    }
    
    private function authUpdate($args) {
        if(!($this->flags&self::auth)){
            $this->putError(403, 'You dont have auth permission');
            return;
        }

        global $db;
        if((isset($args['hostmask']) && strlen($args['hostmask']) > 0) && (isset($args['nick']) && strlen($args['nick']) > 0)) {
            $hostmask = explode('!', $args['hostmask']);
            $sql = "SELECT * FROM streamersettings JOIN streamer using(streamer) WHERE `key` = 'hostmask' AND `value` REGEXP '[A-z0-9]+!" . $db->escape($hostmask[1])  . "';";
            $dbres = $db->query($sql);
            if($dbres && $db->num_rows($dbres) > 0) {
                if($row = $db->fetch($dbres)) {
                    $sql = "INSERT INTO streamersettings (streamer,`key`,value)
                            VALUES (" . $row['streamer'] . ",'hostmask', '" . $db->escape($args['nick'] . "!" . $hostmask[1]) . "')
                            ON DUPLICATE KEY UPDATE value = '" . $db->escape($args['nick'] . "!" . $hostmask[1]) . "';";
                    if($db->execute($sql)) {
                        $this->output['auth']['nick'] = $row['username'];
                        $this->output['auth']['id'] = $row['streamer'];
                        $this->output['auth']['status'] = 0;
                        return;
                    }
                }
            }
        }
        else {
            $this->putError(133, "'authUpdate' needs two arguments [hostmask, nick]!");
        }
        $this->output['auth']['status'] = 1;
    }
    
    private function authJoin($args) {
        if(!($this->flags&self::auth)){
            $this->putError(403, 'You dont have auth permission');
            return;
        }
        
        global $db;
        if(isset($args['hostmask']) && strlen($args['hostmask']) > 0) {
            $hostmask = explode('!', $args['hostmask']);
            $sql = "SELECT * FROM streamersettings JOIN streamer using(streamer) WHERE `key` = 'hostmask' AND `value` REGEXP '[A-z0-9]+!" . $db->escape($hostmask[1])  . "';";
            $dbres = $db->query($sql);
            if($dbres && $db->num_rows($dbres) > 0) {
                if($row = $db->fetch($dbres)) {
                    if($row['hostmask'] != $args['hostmask']) {
                        $sql = "INSERT INTO streamersettings (streamer,`key`,value)
                                VALUES (" . $row['streamer'] . ",'hostmask', '" . $db->escape($args['hostmask']) . "')
                                ON DUPLICATE KEY UPDATE value = '" . $db->escape($args['hostmask']) . "';";
                        $db->execute($sql);
                    }
                    $sql = "INSERT INTO streamersettings (streamer,`key`,value)
                            VALUES (" . $row['streamer'] . ",'isIRC', 1)
                            ON DUPLICATE KEY UPDATE value = 1;";
                    if($db->execute($sql)) {
                        $this->output['auth']['nick'] = $row['username'];
                        $this->output['auth']['id'] = $row['streamer'];
                        $this->output['auth']['status'] = 0;
                        return;
                    }
                }
            }
        }
        else {
            $this->putError(134, "'authJoin' needs one argument [hostmask]!");
        }
        $this->output['auth']['status'] = 1;
    }
    
    private function authPart($args) {
        if(!($this->flags&self::auth)){
            $this->putError(403, 'You dont have auth permission');
            return;
        }
        
        global $db;
        if(isset($args['hostmask']) && strlen($args['hostmask']) > 0) {
            $hostmask = explode('!', $args['hostmask']);
            $sql = "SELECT * FROM streamersettings JOIN streamer using(streamer) WHERE `key` = 'hostmask' AND `value` REGEXP '[A-z0-9]+!" . $db->escape($hostmask[1])  . "';";
            $dbres = $db->query($sql);
            if($dbres && $db->num_rows($dbres) > 0) {
                if($row = $db->fetch($dbres)) {
                    $sql = "INSERT INTO streamersettings (streamer,`key`,value)
                            VALUES (" . $row['streamer'] . ",'isIRC', 0)
                            ON DUPLICATE KEY UPDATE value = 0;";
                    if($db->execute($sql)) {
                        $this->output['auth']['nick'] = $row['username'];
                        $this->output['auth']['id'] = $row['streamer'];
                        $this->output['auth']['status'] = 0;
                        return;
                    }
                }
            }
        }
        else {
            $this->putError(135, "'authPart' needs one argument [hostmask]!");
        }
        $this->output['auth']['status'] = 1;
    }
    
    private function rConfig($args) {
        if(!($this->flags&self::auth)){
            $this->putError(403, 'You dont have auth permission');
            return;
        }
        
        global $db;

        if($this->output['auth']['status'] != 0 || !isset($this->output['auth']['status'])) {
            $this->putError(403, "Auth failed");
            return;
        }
        if(isset($args['key']) && strlen($args['key']) > 0) {
            switch($args['key']) {
                case 'background':
                    $key = 'background';
                    break;
                case 'description':
                    $key = 'defaultshowdescription';
                    break;
                case 'name':
                    $key = 'defaultshowname';
                    break;
                default:
                    $this->putError(136, 'invalid input');
                    return;
            }
        }
        else {
            $this->putError(136, 'invalid input');
            return;
        }
        if(isset($args['value']) && strlen($args['value']) > 0) {
            //update
            $value = $args['value'];
            $sql = "INSERT INTO streamersettings (streamer,`key`,value)
                    VALUES (" . $this->output['auth']['id'] . ",'" . $db->escape($key) ."', '" . $db->escape($value) . "')
                    ON DUPLICATE KEY UPDATE value = '" . $db->escape($value) ."';";
            if($db->execute($sql)) {
                $this->output['rconf']['status'] = 0;
                $this->output['rconf']['key'] = $key;
                $this->output['rconf']['value'] = $value;
            }
        }
        else {
            //output
            $sql = "SELECT * FROM streamersettings JOIN streamer using(streamer) WHERE `key` = '" . $db->escape($key) ."' AND streamer = '" . $db->escape($this->output['auth']['id']) . "';";
            $dbres = $db->query($sql);
            if($dbres && $db->num_rows($dbres) > 0) {
                if($row = $db->fetch($dbres)) {
                    $this->output['rconf']['status'] = 0;
                    $this->output['rconf']['key'] = $key;
                    $this->output['rconf']['value'] = $row['value'];
                }
            }
        }
    }
    
    private function rThread($args) {
        if(!($this->flags&self::auth)){
            $this->putError(403, 'You dont have auth permission');
            return;
        }
        
        global $db;

        if(isset($args['show']) && strlen($args['show']) > 0) {
            $show = $args['show'];
            if(isset($args['thread']) && (is_int((int)$args['thread']))) {
                //update
                $thread = (int)$args['thread'];
                if($this->output['auth']['status'] != 0 || !isset($this->output['auth']['status'])) {
                    $this->putError(403, "Auth failed");
                    return;
                }
                if($thread == 0) {
                    $sql = "UPDATE shows SET thread = NULL WHERE `show` = '" . $db->escape($show) ."' AND streamer = '" . $db->escape($this->output['auth']['id']) ."';";
                }
                else {
                    $sql = "UPDATE shows SET thread = '" . $db->escape($thread) . "' WHERE `show` = '" . $db->escape($show) ."' AND streamer = '" . $db->escape($this->output['auth']['id']) ."';";
                }
                if($db->execute($sql)) {
                    if($db->getAffectedRows() > 0) {
                        $this->output['rthread']['status'] = 0;
                        $this->output['rthread']['show'] = $show;
                        $this->output['rthread']['thread'] = $thread;
                    }
                    else {
                        $this->putError(403, "Auth failed");
                        return;
                    }
                }

            }
            else {
                //output
                $sql = "SELECT thread FROM shows WHERE `show` = '" . $db->escape($show) . "';";
                $dbres = $db->query($sql);
                if($dbres && $db->num_rows($dbres) > 0) {
                    if($row = $db->fetch($dbres)) {
                        $this->output['rconf']['status'] = 0;
                        $this->output['rconf']['show'] = $show;
                        $this->output['rconf']['thread'] = $row['thread'];
                    }
                }
                else {
                    $this->putError(137, 'invalid input');
                    return;
                }
            }
        }
        else {
            $this->putError(137, 'invalid input');
            return;
        }
    }
    
}