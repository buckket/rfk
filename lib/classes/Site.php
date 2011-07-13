<?php
class Site {
    protected $name = 'noname';
    protected $id = 0;


    public static $DISABLE_TEMPLATE = 0;
    public static $RENDER_TEMPLATE = 1;


    public function __construct($id) {

    }

    public function render() {}

    public static function loadSiteByName($name) {
        global $db;

        $sql = 'SELECT site FROM sites WHERE name = "'.$db->escape($name).'" LIMIT 1;';
        $dbres = $db->query($sql);

        if($dbres && $site = $db->fetch($dbres)) {
            return Site::loadSiteById($site['site']);
        }
        return null;
    }

    public static function loadSiteById($id) {
        global $db, $includepath;

        $sql = "SELECT class FROM sites WHERE site = ".$db->escape($id)." LIMIT 1;";
        $dbres = $db->query($sql);
        if($dbres && $site = $db->fetch($dbres)) {
            if(file_exists($includepath.'/sites/'.$site['class'].'.php')) {
                require_once($includepath.'/sites/'.$site['class'].'.php');
                $class = new $site['class']($id);
                return $class;
            }
        }
        return null;
    }
}