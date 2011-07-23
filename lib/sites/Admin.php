<?php

class Admin extends Site {
    public function render() {
        global $template,$urlParams;
        $this->setSideBar();
    }

    private function setSideBar(){
        global $sidebar;
        $url = new UrlParser();
        $url->setParams(array('liqdebug'));
        $sidebar->addEntry('liquidsoap debuglog', $url->makeUrl());
    }

}