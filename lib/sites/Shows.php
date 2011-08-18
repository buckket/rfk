<?php
class Shows extends Site {
    public function render() {
        global $template,$urlParams;
        $params = $urlParams->getParams();

        if(!isset($params[0])) {
            $params[0] = 'overview';
        }
        switch($params[0]) {
            case 'data':
                $this->printJSONData((int)$_POST['start'],(int)$_POST['end']);
                return Site::$DISABLE_TEMPLATE;
            case 'calendar':
                $template->setTemplate('shows/calendar.html');
                $template->addData('feedurl', 'show/data-calendar/');
                $this->setSideBar();
                return Site::$RENDER_TEMPLATE;
            case 'overview':
            default:
                $template->setTemplate('shows/overview.html');
                $this->setSideBar();
                return Site::$RENDER_TEMPLATE;
        }

    }


    private function printJSONData($start,$end) {
        global $db;
        $sql = 'SELECT UNIX_TIMESTAMP(begin) as begin, UNIX_TIMESTAMP(end) as end,name
                  FROM shows
                 WHERE (UNIX_TIMESTAMP(begin) BETWEEN '.$db->escape($start).' AND '.$db->escape($end).'
                    OR UNIX_TIMESTAMP(end)  BETWEEN '.$db->escape($start).' AND '.$db->escape($end).')
                    AND type = "PLANNED"';
        $dbres = $db->query($sql);
        if($dbres) {
            $events = array();
            while($event = $db->fetch_assoc($dbres)) {
                $events[] = array('start' => (int)$event['begin'],
                                  'end'   => (int)$event['end'],
                                  'title' => $event['name'],
                                  'allDay'=> false);
            }
            echo json_encode($events);
        }
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