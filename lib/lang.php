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

    public function lang ($key, $args = array()) {
        if(!isset($this->lang[$key]))
            return $key;
        return vsprintf($this->lang[$key],$args);
    }

    public function getAvailLangs () {
        global $db;
        $sql = "SELECT * FROM locales ORDER BY language ASC;";
        $dbres = $db->query($sql);
        $out = array();
        if($dbres) {
            while($row = $db->fetch($dbres)) {
                $out[] = array('country' => $row['country'], 'locale' =>  $row['locale'],'name' => $row['name']);
            }
        }
        return $out;
    }
}