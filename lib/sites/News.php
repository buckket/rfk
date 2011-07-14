<?php

class News extends Site {
    public function render() {
        global $template,$urlParams;

        $template->setTitle('News');
        $template->setTemplate('news.html');
        if(count($urlParams->getParams()) == 0) {
            $template->addData('news',$this->getNews());
        } else {
            $params = $urlParams->getParams();
            switch($params[0]) {
                case 'post':
                    break;
                default:
                    $template->addData('news',$this->getNews(count($params) == 2?$params[1]:0));
            }
        }
        return Site::$RENDER_TEMPLATE;
    }

    private function getNews($page = 1) {
        global $db;
        $pagesize = 5;

        $begin = (((int)$page)-1)*$pagesize;
        if($begin < 0)
            $begin = 0;
        $sql = 'SELECT * FROM news JOIN streamer USING (streamer) LIMIT '.$begin.','.$pagesize;
        $dbres = $db->query($sql);
        $news = array();
        if ($dbres) {
            while($item = $db->fetch($dbres)) {
                $news[] = array('time' => $item['time'], 'username' => $item['username'], 'text' => $item['text']);
            }
        }
        return $news;
    }
}