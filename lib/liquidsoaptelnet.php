<?php

class Liquidsoap {
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
        $this->disconnect();
    }

    public function getHarborSource(){
        preg_match_all('/^| (src_[0-9]+)/', $this->executeCommand('help'), $matches);
        $this->harbor = $matches[1][1];
    }

    public function kickHarbor() {
        $this->executeCommand($this->harbor.".kick");
    }
    public function getHarborStatus() {
        $this->executeCommand($this->harbor.".status");
    }

    protected function executeCommand($command){
        if(!$this->sock) {
            return false;
        }
        fwrite($this->sock,$command."\n");
        $out = '';
        $s = '';
        while(!feof($this->sock) && $s != "END\n"){
            $s = fgets($this->sock, 4096);
            $out .= $s;
        }
        return $out;
    }
}
?>
