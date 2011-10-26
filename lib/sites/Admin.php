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
                $template->addData('liq', $this->getLiquidsoapStatus());

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
        $info = array();
        if($liq->connect()) {
            $info['status'] = true;
            $info['uptime'] = $liq->getUptime();
            $harbor = $liq->getHarborSource();
            $info['harbor']['status'] = preg_match('/^source client connected/',$liq->getHarborStatus($harbor));
            $info['harbor']['statuslong'] = $liq->getHarborStatus($harbor);
            $streams = $liq->getOutputStreams();
            $outputs = array();
            foreach($streams as $stream) {
                $output = array();
                $output['name'] = $stream;
                $status = $liq->getOutputStreamStatus($stream);
                if($status == 'on') {
                    $output['status'] = true;
                } else {
                    $output['status'] = false;
                }
                $output['statuslong'] = $liq->getOutputStreamStatus($stream);
                $outputs[] = $output;
            }
            $info['outputs'] = $outputs;
        } else {
            $info['liq_running'] = false;
        }
        return $info;
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