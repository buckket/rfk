<?php

class Admin extends Site {
    public function render() {
        global $template,$urlParams;
        $params = $urlParams->getParams();
        $template->setTemplate('admin/admin.html');
        $this->setSideBar();
        return Site::$RENDER_TEMPLATE;
    }

    private function setSideBar(){
        global $sidebar;
        $url = new UrlParser('admin');
        $url->setParams(array('liqdebug'));
        $sidebar->addEntry('liquidsoap debuglog', $url->makeUrl());
    }

}