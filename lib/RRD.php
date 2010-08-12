<?php
class RRD{
	var $server = 0;
	var $workingdir = "./";
	var $step = 300;
	function __construct($wd,$server){
		$this->workingdir = $wd."rrd/";
		$this->server = $server;
		$this->checkdir();
	}
	
	public function create($ds,$rra){
		if(file_exists($this->workingdir.$this->server.".rrd")){
			return false;
		}
		$cmd = "rrdtool create ".$this->server.".rrd --step ".$this->step." ";
		foreach($ds as $d){
			$d = new RRDDS($d[0],$d[1],$d[2],$d[3],$d[4]);
			$cmd .= $d->toString()." ";
		}
		foreach($rra as $r){
			$r = new RRDRRA($r[0],$r[1],$r[2],$r[3]);
			$cmd .= $r->toString()." ";
		}
		//echo $cmd;
		$this->command($cmd,$this->workingdir,false);
	}
	public function update($names,$values){
		$cmd = "rrdtool update ".$this->server.".rrd N";
		foreach($names as $name){
			if(isset($values[$name])){
				$cmd .= ":".$values[$name];
			}else{
				$cmd .= ":U";
			}
		}
		//echo $cmd;
		$this->command($cmd,$this->workingdir,'echo');
	}
	public function createGraph($blah = false){
		$cmd = 'rrdtool graph - --end now --start end-120000s --width 400';
		$cmd .= ' DEF:ds0a='.$this->workingdir.$this->server.'.rrd:players:AVERAGE';
		$cmd .= ' DEF:ds0b='.$this->workingdir.$this->server.'.rrd:cpu:AVERAGE:step=1800';
    	$cmd .= ' DEF:ds0c='.$this->workingdir.$this->server.'.rrd:mem:AVERAGE:step=7200';
    	$cmd .= ' LINE1:ds0a#0000FF:"Spieler\l"';
    	$cmd .= ' LINE1:ds0b#00CCFF:"CPU\l"';
    	$cmd .= ' LINE1:ds0c#FF00FF:"mem\l"';
		if($blah == 'true'){
			$cmd = 'rrdtool graph - --end now --start end-1400s --width 800';
			$cmd .= ' DEF:packets='.$this->workingdir.$this->server.'.rrd:packets:AVERAGE';
			$cmd .= ' DEF:kills='.$this->workingdir.$this->server.'.rrd:kills:AVERAGE';
			$cmd .= ' DEF:user='.$this->workingdir.$this->server.'.rrd:user:AVERAGE';
			$cmd .= ' DEF:chat='.$this->workingdir.$this->server.'.rrd:chat:AVERAGE';
			$cmd .= ' DEF:useronline='.$this->workingdir.$this->server.'.rrd:useronline:AVERAGE';
			$cmd .= ' DEF:credits='.$this->workingdir.$this->server.'.rrd:credits:AVERAGE';
			$cmd .= ' DEF:serveralive='.$this->workingdir.$this->server.'.rrd:serveralive:AVERAGE';
			$cmd .= ' DEF:server='.$this->workingdir.$this->server.'.rrd:server:AVERAGE';
			$cmd .= ' LINE1:packets#0000FF:"packets\l"';
			$cmd .= ' LINE1:kills#00CCFF:"kills\l"';
			$cmd .= ' LINE1:user#FF00FF:"users\l"';
			$cmd .= ' LINE1:useronline#00FFCC:"users\l"';
			$cmd .= ' LINE1:credits#0099FF:"credits\l"';
			$cmd .= ' LINE1:serveralive#FF99FF:"serveralive\l"';
		}
		//echo $cmd;
		return shell_exec($cmd);
	}
	
	private function checkdir(){
		if(!is_dir($this->workingdir)){
			echo "creating folder";
			shell_exec("mkdir -p ".$this->workingdir);
		}
	}
	
	
	protected function command($cmd,$cwd,$linecallback = false){
		if($this->serverid !== false && $this->groupid !== false){
			$ds = array(
			   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
   			   2 => array("pipe", "w"),  // stdout is a pipe that the child will write to
			);
			$process = proc_open($cmd, $ds, $pipes, $cwd);
			if($process === false){
				return false;
			}
			$read_output = $read_error = false;
			$buffer_len  = $prev_buffer_len = 0;
			$ms          = 10;
			$output      = '';
			$read_output = true;
			$error       = '';
			$read_error  = true;
			stream_set_blocking($pipes[1], 0);
			stream_set_blocking($pipes[2], 0);
			$lastoutput = '';
			while ($read_error != false or $read_output != false){
				if ($read_output != false){
					if(feof($pipes[1])){
						fclose($pipes[1]);
						$read_output = false;
					}else{
						$str = fgets($pipes[1], 1024);
						$len = strlen($str);
						if ($len){
							//echo $str."<br>";
							if(!preg_match('/[\r\n]/',$str)){
								$lastoutput = $str;
							}else{
								//$output .= $lastoutput.$str;
								if($linecallback !== false){
									$this->$linecallback($lastoutput.$str);
								}
								$lastoutput = '';
							}
							$buffer_len += $len;
						}
					}
				}
				if ($read_error != false){
					if(feof($pipes[2])){
						fclose($pipes[2]);
						$read_error = false;
					}else{
						$str = fgets($pipes[2], 1024);
						$len = strlen($str);
						if ($len){
							echo $str."<br>";
							$error .= $str;
							$buffer_len += $len;
						}
					}
				}
				if ($buffer_len > $prev_buffer_len){
					$prev_buffer_len = $buffer_len;
					$ms = 10;
				}else{
					usleep($ms * 1000); // sleep for $ms milliseconds
					if ($ms < 160){
						$ms = $ms * 2;
					}
				}
			}
			return proc_close($process);
		}else{
			return false;	
		}
	}
}
//DS:ds-name:GAUGE | COUNTER | DERIVE | ABSOLUTE:heartbeat:min:max
class RRDDS{
	var $heartbeat;
	var $min;
	var $max;
	var $name;
	var $type;
	const gauge = 'GAUGE';
	const counter = 'COUNTER';
	const derive = 'DERIVE';
	const abs = 'ABSOLUTE';
	
	function __construct($name, $type, $hb, $min,$max){
		$this->heartbeat = $hb;
		$this->name = $name;
		$this->min = $min;
		$this->max = $max;
		$this->type = $type;
	}
	public function toString(){
		return "DS:".$this->name.':'.$this->type.':'.$this->heartbeat.':'.$this->min.':'.$this->max;
	}
}

//RRA:AVERAGE | MIN | MAX | LAST:xff:steps:rows
class RRDRRA{
	var $xff;
	var $steps;
	var $rows;
	var $type;
	const min = 'MIN';
	const max = 'MAX';
	const last = 'LAST';
	const avg = 'AVERAGE';
	
	function __construct($type, $xff, $steps,$rows){
		$this->xff = $xff;
		$this->steps = $steps;
		$this->rows = $rows;
		$this->type = $type;
	}
	public function toString(){
		return "RRA:".$this->type.':'.$this->xff.':'.$this->steps.':'.$this->rows;
	}
}

?>