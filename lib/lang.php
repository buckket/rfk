<?php
class Lang {
    private $langcode = 'de';
    private $lang = array();
    public function __construct($lang) {
        $this->langcode = $lang;
        $this->loadContext('common');
    }

    public function loadContext ($context) {
        global $includepath;
        $filename = $includepath.'/Lang/'.$this->langcode.'/'.$context.'.php';
        if(file_exists($filename)) {
            include $filename;
            $this->lang = array_merge($this->lang,$lang);
        }else{
            echo $filename;
        }
    }
    public function getLang() {
        return $this->lang;
    }
}