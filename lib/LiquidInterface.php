<?php

class LiquidInterface {
    protected $sock;

    protected $harbor;

    public function __construct() {

    }

    public function connect(){
        $this->sock = fsockopen('localhost',1234,$error,$errstr,10);
        if(!$this->sock){
            error_log("Error communicating with liquidsoap; $error: $errstr.");
        }else{
            return true;
        }
        return false;
    }

    public function disconnect(){
        if($this->sock) {
            fclose($this->sock);
        }
    }
    public function __destruct() {
        $this->executeCommand('exit');
        $this->disconnect();
    }

    public function getHarborSource(){
        if($this->sock) {
            preg_match_all('/^(harbor_[0-9]+)/m', $this->executeCommand('list'), $matches);
            return $matches[0][0];
        }
        return null;
    }

    public function getOutputStreams(){
        if($this->sock) {
            preg_match_all('/^(.*?) : output/m', $this->executeCommand('list'), $matches);
            return $matches[1];
        }
        return null;
    }

    public function getOutputStreamStatus($output){
        return $this->executeCommand($output.".status");
    }

    public function startOutputStream($output){
        return $this->executeCommand($output.".start");
    }

    public function stopOutputStream($output){
        return $this->executeCommand($output.".stop");
    }

    public function kickHarbor($source) {
        $this->executeCommand($source.".kick");
    }
    public function getHarborStatus($source) {
        return $this->executeCommand($source.".status");
    }

    public function getUptime() {
        return $this->executeCommand('uptime');
    }

    protected function executeCommand($command){
        if(!$this->sock) {
            return false;
        }
        fwrite($this->sock,$command."\n");
        $out = '';
        $s = '';
        while(!feof($this->sock) && !preg_match('/^END/m',$s)){
            $s = fgets($this->sock, 4096);
            $out .= $s;
        }
        $out = preg_replace('/END$/', '', trim($out));
        $out = trim($out);
        return $out;
    }
}
?>
