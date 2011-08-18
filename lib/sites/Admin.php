<?php

class Admin extends Site {
    public function render() {
        global $template,$urlParams;
        $params = $urlParams->getParams();
        if(!isset($params[0])) {
            $params[0] = 'overview';
        }
        switch($params[0]) {
            case 'liqstatus':
                $template->setTemplate('admin/liquidstatus.html');
                $this->getLiquidsoapStatus();
                $this->setSideBar();
                break;
            case 'overview':
            default:
                $template->setTemplate('admin/admin.html');
                $this->setSideBar();
                return Site::$RENDER_TEMPLATE;
        }
        return Site::$RENDER_TEMPLATE;
    }

    private function getLiquidsoapStatus() {
        require_once dirname(dirname(__FILE__)).'/classes/LiquidInterface.php';
        $liq = new LiquidInterface();
        if($liq->connect()) {
            $liq->getHarborSource();
            $liq->getHarborStatus();
        } else {
            echo 'hng';
        }
    }

    private function setSideBar(){
        global $sidebar;
        $url = new UrlParser('admin');
        $url->setParams(array('liqdebug'));
        $sidebar->addEntry('liquidsoap debuglog', $url->makeUrl());
        $url->setParams(array('liqstatus'));
        $sidebar->addEntry('liquidsoap status', $url->makeUrl());
    }

}