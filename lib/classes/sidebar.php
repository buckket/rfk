<?php
class Sidebar {

    private $searchurl = null;
    private $searchname = null;

    private $entries = array();

    public function __construct() {

    }

    public function addEntry( $name, $url) {
        $this->entries[] = array( 'name' => $name, 'url' => $url);
    }

    public function setSeach($searchurl, $searchname){
        $this->searchurl = $searchurl;
        $this->searchname = $searchname;
    }

    public function pushToTemplate(){
        global $template;
        $sidebar = array();
        $sidebar['has_search'] = isset($this->searchurl);
        $sidebar['search'] = array('name' => $this->searchname,'url' => $this->searchurl);
        $sidebar['entries'] = $this->entries;
        $template->addData('sidebar',$sidebar);
    }
}