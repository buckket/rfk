<?php
class RRD{
    var $filename = 0;
    var $workingdir = "./";
    var $step = 120;
    function __construct($wd,$filename){
        $this->workingdir = $wd;
        $this->filename = $filename;
        $this->checkdir();
    }

    public function create($ds,$rra,$start = false){
        if(!$start) {
            $start = time();
        }
        if(file_exists($this->workingdir.$this->filename.".rrd")){
            return false;
        }
        $cmd = "rrdtool create ".$this->filename.".rrd --start $start --step ".$this->step." ";
        foreach($ds as $d){
            $cmd .= $d->toString()." ";
        }
        foreach($rra as $r){
            $cmd .= $r->toString()." ";
        }
        //echo $cmd;
        $this->command($cmd,$this->workingdir,false);
        return true;
    }
    public function update($names,$values,$time = 'N'){
        $cmd = "rrdtool update ".$this->filename.".rrd $time";
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
    public function createGraph($defs,$graphs,$cdefs = array(),$vdefs = array()){
        $cmd = 'rrdtool graph - --end now --start end-2days --width 450';
        foreach ($defs as $def){
            $cmd .= ' '.$def->toString();
        }
        foreach ($cdefs as $cdef){
            $cmd .= ' '.$cdef->toString();
        }
        foreach ($graphs as $graph){
            $cmd .= ' '.$graph->toString();
        }
        //echo $cmd;
        //$cmd .= ' DEF:ds0a='.$this->workingdir.$this->filename.'.rrd:listener:LAST';
        //$cmd .= ' DEF:ds0b='.$this->workingdir.'2.rrd:listener:LAST';
        //$cmd .= ' DEF:ds0c='.$this->workingdir.'4.rrd:listener:LAST';
        //$cmd .= ' CDEF:ds0d=ds0a,ds0b,+';
        //$cmd .= ' LINE1:ds0a#0000FF:"listener\l"';
        //$cmd .= ' LINE1:ds0b#00FFFF:"listener\l"';
        //$cmd .= ' LINE1:ds0c#FF00FF:"listener\l"';
        //$cmd .= ' LINE1:ds0d#FF00FF:"gesammt\l"';
        return shell_exec($cmd);
    }

    private function checkdir(){
        if(!is_dir($this->workingdir)){
            echo "creating folder";
            shell_exec("mkdir -p ".$this->workingdir);
        }
    }


    protected function command($cmd,$cwd,$linecallback = false){
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

class RRDDEF{
    var $step;
    var $start;
    var $end;
    var $name;
    var $rrd;
    var $dsname;
    var $cf;
    public function __construct($name,$rrd, $dsname, $cf, $step = null, $start = null, $end = null) {
        $this->name = $name;
        $this->rrd = $rrd;
        $this->dsname = $dsname;
        $this->cf = $cf;
        $this->step = $step;
        $this->start = $start;
        $this->end = $end;
    }
    public function toString(){
        $ret =  "DEF:".$this->name.'='.$this->rrd.':'.$this->dsname.':'.$this->cf;
        if($this->step) {
            $ret .= ':'.$this->step;
        }
        if($this->start) {
            $ret .= ':'.$this->start;
        }
        if($this->end) {
            $ret .= ':'.$this->end;
        }
        return $ret;
    }
}

class RRDLINE{
    var $width;
    var $var;
    var $color;
    var $legend;

    public function __construct($width, $var, $color,$legend) {
        $this->width = $width;
        $this->var = $var;
        $this->color = $color;
        $this->legend = $legend;
    }

    public function toString(){
        $ret =  'LINE'.$this->width.':'.$this->var.'#'.$this->color.':'.$this->legend;
        return $ret;
    }
}
class RRDCDEF{
    var $cmd;
    var $var;

    public function __construct($var, $cmd) {
        $this->cmd = $cmd;
        $this->var = $var;
    }

    public function toString(){
        $ret =  'CDEF:'.$this->var.'='.implode(',',$this->cmd);
        return $ret;
    }
}
?>