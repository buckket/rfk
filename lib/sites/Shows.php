<?php
class Shows extends Site {
    public function render() {
        global $template,$urlParams;
        $params = $urlParams->getParams();

        if(!isset($params[0])) {
            $params[0] = 'overview';
        }
        switch($params[0]) {
            case 'overview':
            default:
                $template->setTemplate('shows/overview.html');
        }
        $this->setSideBar();
        return Site::$RENDER_TEMPLATE;
    }

    private function setSideBar(){
        global $sidebar;
        $url = new UrlParser('show');
        $url->setParams(array('add'));
        $sidebar->addEntry('Add a show', $url->makeUrl());
        $url->setParams(array('calendar'));
        $sidebar->addEntry('Calendar', $url->makeUrl());
        $url->setParams(array('next'));
        $sidebar->addEntry('Upcoming shows', $url->makeUrl());
        $url->setParams(array('series'));
        $sidebar->addEntry('Series', $url->makeUrl());
    }

}