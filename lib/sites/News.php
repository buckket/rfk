<?php

class News extends Site {
    public function render() {
        global $template,$urlParams;

        $template->setTitle('News');
        $template->setTemplate('news.html');
        if(count($urlParams->getParams()) == 0) {
            $template->addData('news',$this->getNews(1, $totalPages));
            $template->addData('pages',$this->pageHandler(1, $totalPages));
        } else {
            $params = $urlParams->getParams();
            switch($params[0]) {
                case 'post':
                    break;
                default:
                    $template->addData('news',$this->getNews(count($params) == 2?$params[1]:1, $totalPages));
                    $template->addData('pages',$this->pageHandler(count($params) == 2?$params[1]:1, $totalPages));
            }
        }
        return Site::$RENDER_TEMPLATE;
    }

    private function pageHandler($currpage,$totalPages){
        $pages = array();
        $url = new UrlParser('news');
        if($currpage < $totalPages) {
            $url->setParams(array('page',$currpage+1));
            $pages['prev'] = $url->makeUrl();
        }
        if($currpage != 1) {
            $url->setParams(array('page',$currpage-1));
            $pages['next'] = $url->makeUrl();
        }
        return $pages;
    }

    private function getNews($page = 1,&$totalPages) {
        global $db;
        $pagesize = 5;

        $begin = (((int)$page)-1)*$pagesize;
        if($begin < 0)
            $begin = 0;
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM news JOIN streamer USING (streamer) LIMIT '.$begin.','.$pagesize;
        $dbres = $db->query($sql);
        $news = array();
        if ($dbres) {
            $totalPages = ceil($db->getFoundRows()/$pagesize);
            while($item = $db->fetch($dbres)) {
                $news[] = array('time' => $item['time'], 'username' => $item['username'], 'text' => $item['text']);
            }
        }
        return $news;
    }
}