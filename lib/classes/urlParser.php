<?php
class UrlParser {

    private $site = false;

    private $params = array();

    public function __construct() {

    }

    public function parseUrl() {
        $matches = array();
        if(isset($_GET['params'])) {
            $params = $_GET['params'];
            preg_match('|^(?P<site>.*?)(/(?P<params>.*?))?$|', $params, $matches);
        }

        if(isset($matches['site'])) {
            $this->site = $matches['site'];
        }

        if(isset($matches['params'])) {
            $this->params = explode('-',$matches['params']);
        }
    }

    public function getSite() {
        return $this->site;
    }

    public function setSite($site) {
        $this->site = $site;
    }

    public function getParams() {
        return $this->params;
    }

    public function setParams($array) {
        $this->params = $array;
    }

    private function encodeParams() {
        $out = array();
        foreach($this->params as $param) {
            $out[] = str_replace('-', '_', $param);
        }
        return implode('-',$out);
    }
}