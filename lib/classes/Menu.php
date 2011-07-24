<?php
class Menu {
    public function __construct() {

    }

    private function getItems () {
        global $user, $db;

        $sql = 'SELECT site, code, name
                  FROM sites ';
        $sql .= 'WHERE privilege IS NULL ';
        $sql .= ' GROUP BY site
        		ORDER BY sites.sort;';
        $dbres = $db->query($sql);
        $menu = array();
        if($dbres) {
            while($item = $db->fetch($dbres)) {

                $url = new UrlParser($item['code']);
                $menu[] = array('name' => $item['name'],'url' =>$url->makeUrl());
            }
        }
        if($user->isLoggedIn()) {
            $sql = 'SELECT site, code, name
                  FROM sites ';
            $sql .= 'JOIN streamer_privilege USING (privilege)
                  LEFT JOIN streamer USING (streamer)
        		 WHERE streamer = '.$user->getUserId().'
        		    OR privilege IS NULL ';
            $sql .= ' GROUP BY site
        		ORDER BY sites.sort;';
            $dbres = $db->query($sql);
            if($dbres) {
                while($item = $db->fetch($dbres)) {

                    $url = new UrlParser($item['code']);
                    $menu[] = array('name' => $item['name'],'url' =>$url->makeUrl());
                }
            }
        }
        return $menu;
    }

    public function pushToTemplate () {
        global $template;
        $template->addData('menu', $this->getItems());
    }
}