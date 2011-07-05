<?php

class News extends Site {
    public function render() {
        global $template;

        $template->setTitle('News');
        $template->setTemplate('base.html');
        return Site::$RENDER_TEMPLATE;
    }
}