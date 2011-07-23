<?php
class UserSite extends Site {
    public function render() {
        global $template,$urlParams;
        $params = $urlParams->getParams();
        if(!isset($params[0]))
        $params[0] = 'overview';

        switch($params[0]) {
            case 'overview':
            default:
                $template->setTitle('Djs');
                $template->setTemplate('blinkenworld.html');
        }

        return Site::$RENDER_TEMPLATE;
    }

    public function getTopDjs() {
        $sql = 'SELECT SUM(TIMESTAMPDIFF(SECOND,begin,end)) as s, streamer FROM shows GROUP BY streamer ORDER BY s DESC';
    }
}