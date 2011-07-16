<?php
class Tracks extends Site {
    public function render() {
        global $template,$urlParams;
        $params = $urlParams->getParams();
        if(!isset($params[0]))
        $params[0] = 'overview';

        switch($params[0]) {
            case 'title':
                $template->setTemplate('tracks/title.html');
                $template->addData('title',$this->getTitleInfo($params[1]));
                if($params[1]) {
                    $template->addData('djs',$this->getTitleDjs($params[1]));
                }
                break;
            case 'artist':
                $template->setTemplate('tracks/artist.html');
                $template->addData('artist',$this->getArtistInfo($params[1]));
                if($params[1]) {
                    $template->addData('tracks',$this->getArtistTracks($params[1]));
                    $template->addData('djs',$this->getArtistDjs($params[1]));
                }
                break;
            case 'overview':
            default:
                $template->setTemplate('tracks/overview.html');
                $template->addData('mtracks',$this->getMostPlayedTracks());
                $template->addData('martists',$this->getMostPlayedArtists());
        }
        return Site::$RENDER_TEMPLATE;
    }

    private function getArtistInfo(&$artistid){
        global $db;
        $sql = 'SELECT name FROM artists WHERE artist = '.$db->escape($artistid);
        $dbres = $db->query($sql);
        $artist = array();
        if($dbres) {
            if($artist = $db->fetch($dbres)) {

            } else {
                $artistid = false;
            }
        } else {
            $artistid = false;
        }
        return $artist;
    }

    private function getArtistTracks($artistid) {
        global $db;
        $sql = 'SELECT count( t.title ) AS c, t.name AS tname, t.title AS tid, a.name as aname
				  FROM songhistory
				  JOIN titles AS t ON ( titleid = t.title )
				  JOIN artists AS a ON ( t.artist = a.artist )
				WHERE t.artist = '.$db->escape($artistid).'
				GROUP BY t.title
				ORDER BY count( t.title ) DESC';
        $dbres = $db->query($sql);
        $titles = array();
        if($dbres) {
            $url = new UrlParser('tracks');
            while($title = $db->fetch($dbres)) {
                $url->setParams(array('title',$title['tid'],$title['aname'],$title['tname']));
                $titles[] = array('url' => $url->makeUrl(),
                                'title' => $title['tname'],
                                'count' => $title['c']);

            }
        }
        return $titles;
    }

    private function getArtistDjs($artistid) {
        global $db;
        $sql = 'SELECT count(streamer) AS c, username, streamer
  				  FROM streamer
  				  JOIN shows USING (streamer)
  				  JOIN songhistory USING (`show`)
  				  JOIN titles ON (titles.title = titleid)
				WHERE titles.artist = '.$db->escape($artistid).'
				GROUP BY streamer
				ORDER BY count(streamer) DESC';
        $dbres = $db->query($sql);
        $djs = array();
        if($dbres) {
            $url = new UrlParser('user');
            while($dj = $db->fetch($dbres)) {
                $url->setParams(array('info',$dj['streamer'],$dj['username']));
                $djs[] = array('url' => $url->makeUrl(),
                                'name' => $dj['username'],
                                'count' => $dj['c']);

            }
        }
        return $djs;
    }

    private function getTitleInfo(&$titleid){
        global $db;
        $sql = 'SELECT titles.name as tname, artists.name as aname, artists.artist as aid
        		  FROM titles
        		  JOIN artists USING (artist)
        		 WHERE title = '.$db->escape($titleid);
        $dbres = $db->query($sql);
        $title = array();
        if($dbres) {
            if($title = $db->fetch($dbres)) {
                $url = new UrlParser('tracks');
                $url->setParams(array('artist',$title['aid'],$title['aname']));
                $title['aurl'] = $url->makeUrl();
            } else {
                $title = false;
            }
        } else {
            $title = false;
        }
        return $title;
    }

    private function getTitleDjs($titleid) {
        global $db;
        $sql = 'SELECT count(streamer) AS c, username, streamer
  				  FROM streamer
  				  JOIN shows USING (streamer)
  				  JOIN songhistory USING (`show`)
  				  JOIN titles ON (titles.title = titleid)
				WHERE titles.title = '.$db->escape($titleid).'
				GROUP BY streamer
				ORDER BY count(streamer) DESC';
        $dbres = $db->query($sql);
        $djs = array();
        if($dbres) {
            $url = new UrlParser('user');
            while($dj = $db->fetch($dbres)) {
                $url->setParams(array('info',$dj['streamer'],$dj['username']));
                $djs[] = array('url' => $url->makeUrl(),
                                'name' => $dj['username'],
                                'count' => $dj['c']);

            }
        }
        return $djs;
    }

    private function getMostPlayedArtists() {
        global $db;

        $sql = "SELECT count(t.title) as c, t.name as tname, a.name as aname, a.artist as aid, t.title as tid
				  FROM songhistory
				  JOIN titles as t ON (titleid = t.title)
				  JOIN artists as a ON (t.artist = a.artist)
				WHERE a.flag IS NULL OR a.flag & ~1
				GROUP BY a.artist
				ORDER BY count(a.artist) desc
				LIMIT 0,25";
        $dbres = $db->query($sql);
        $artists = array();
        if($dbres) {
            $url = new UrlParser('tracks');
            while($artist = $db->fetch($dbres)) {
                $url->setParams(array('artist',$artist['aid'],$artist['aname']));
                $artists[] = array('url' => $url->makeUrl(),
                                'artist' => $artist['aname'],
                                'count' => $artist['c']);

            }
        }
        return $artists;
    }
    private function getMostPlayedTracks() {
        global $db;

        $sql = "SELECT count(t.title) as c, t.name as tname, a.name as aname, a.artist as aid, t.title as tid
				  FROM songhistory
				  JOIN titles as t ON (titleid = t.title)
				  JOIN artists as a ON (t.artist = a.artist)
				WHERE a.flag IS NULL OR a.flag & ~1
				GROUP BY t.title
				ORDER BY count(t.title) desc
				LIMIT 0,25";
        $dbres = $db->query($sql);
        $titles = array();
        if($dbres) {
            $url = new UrlParser('tracks');
            while($title = $db->fetch($dbres)) {
                $url->setParams(array('title',$title['tid'],$title['aname'],$title['tname']));
                $titles[] = array('url' => $url->makeUrl(),
                                'title' => $title['tname'],
                                'artist' => $title['aname'],
                                'count' => $title['c']);

            }
        }
        return $titles;
    }
}