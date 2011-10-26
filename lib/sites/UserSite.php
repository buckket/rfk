<?php
class UserSite extends Site {
    public function render() {
        global $template,$urlParams;
        $params = $urlParams->getParams();
        if(!isset($params[0]))
            $params[0] = 'overview';

        //some checkings

        switch($params[0]) {
            case 'info':
                break;
            case 'settings':
                global $user;
                if($user->isLoggedIn()){
                    $template->addData('accsettings', $this->getAccountSettings());
                    $template->addData('showsettings', $this->getShowSettings());
                    $template->setTemplate('user/settings.html');
                } else {
                    //error handling...
                }
                break;
            case 'overview':
            default:
                $template->setTitle('Djs');
                $template->setTemplate('blinkenworld.html');
        }

        return Site::$RENDER_TEMPLATE;
    }

    private function getAccountSettings() {
        global $db;
    }

    private function getShowSettings() {
        global $db;
    }

    public function getTopDjs() {
        $sql = 'SELECT SUM(TIMESTAMPDIFF(SECOND,begin,end)) as s, streamer FROM shows GROUP BY streamer ORDER BY s DESC';
    }
}