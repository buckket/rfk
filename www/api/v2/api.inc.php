<?php

$basePath = dirname(dirname(dirname(dirname(__FILE__))));
require_once $basePath.'/lib/common.inc.php';
/**
 * APIclass
 * 
 * Errorhandling:
 * 
 * Errorcode	Description
 * -----------------------------
 * 1			Error while parsing querys
 * 
 * 8			Unknown apicall
 * 9			Error in apicall
 * 
 * 
 * @author teddydestodes
 *
 */
class Api {
	
	var $querys = array();
	
	var $users = array();
	var $shows = array();
	
	var $output = array();
	var $error = array();
	
	var $flags = 0;
	
	/**
	 * This array is used to get the corresponding Function to an api call
	 * 
	 * @var array
	 */
	var $queryhooks = array('dj'        => 'putDj',
	                        'nextshows' => 'putNextShows',
							'lastshows' => 'putLastShows');
	
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
	public function __construct($flags = 0) {
		
		$this->flags = $flags;
		
		$this->parseGET();
		foreach($this->querys as $query => $args) {
			if($this->state > 0){
				echo 'nigger';
				break;
			}
			$this->doQuery($query,$args);
		}
	}
	
	private function doQuery($name,$args){
		if(strlen($this->queryhooks[$name]) > 0){
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
	
	private function putShow($id, $name,$description,$type,$dj,$thread,$begin,$end){
		$this->shows[(int)$id] = array('name' => $name,
		                               'description' => $description,
		                               'begin'  => (int)$begin,
		                               'end'    => (int)$end,
									   'type'   => $type,
									   'dj'     => (int)$dj,
									   'thread' => (int)$thread);
	}
	
	private function putError($code, $message){
		$this->state = 1;
		$this->error = array('code' => $code, 'message' => $message);
	}
	
	private function parseGET(){
		foreach($_GET as $name => $query){
			$qry = array();
			$qtmp = explode(':', $query);
			if(count($qtmp) == 1){
				$qry[$qtmp[0]] = true;
			} else if(count($qtmp) == 2){
				$qry[$qtmp[0]] = $qtmp[1];
			} else {
				$this->putError(1, 'Argument with 2 Values! ('.$name.'['.$qtmp[0].'])');
				
			}
			$this->querys[$name] = $qry;
		}
		//print_r($this->querys);
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
	    if($dbres) {
	        while($row = $db->fetch($dbres)) {
	            $this->putUser($row['streamer'], $row['username']);
	            $this->putShow($row['show'], $row['name'], $row['description'], $row['type'], $row['streamer'], $row['thread'], $row['b'], $row['e']);
	            $this->output['lastshows'][] = (int)$row['show'];
	        }
	    }
	}
	
	function putDJ($args){
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
}