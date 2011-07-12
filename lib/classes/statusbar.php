<?php
class StatusBar {
    function __construct() {
    }

    public function pushToTemplate() {
        global $template;
        $template->addData('uptime', exec('uptime'));
    }
}