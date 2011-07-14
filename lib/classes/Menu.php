<?php
class Menu {
    public function __construct() {

    }

    private function getItems () {
        global $user, $db;

        $sql = 'SELECT site, code, name
                  FROM sites ';
        if($user->isLoggedIn()) {
            $sql .= 'JOIN streamer_privilege USING (privilege)
                  JOIN streamer USING (streamer)
        		 WHERE streamer = '.$user->getUserId().' ';
        } else {
            $sql .= 'WHERE privilege IS NULL ';
        }
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
        return $menu;
    }

    public function pushToTemplate () {
        global $template;
        $template->addData('menu', $this->getItems());
    }
}