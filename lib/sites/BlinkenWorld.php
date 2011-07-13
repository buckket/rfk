<?php
class Blinkenworld extends Site {
    public function render() {
        global $template;
        $template->setTitle('BlinkenWorld');
        $template->setTemplate('blinkenworld.html');
        return Site::$RENDER_TEMPLATE;
    }
}