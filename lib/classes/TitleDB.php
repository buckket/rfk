<?php

class TitleDB {
    function __construct() {

    }

    function getTopArtists() {
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
}