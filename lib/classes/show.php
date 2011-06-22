<?php
/**
 * Database abstraction for Shows
 * @author teddydestodes
 * @TODO stub
 */
class Show {

    /**
     * uid of the Show
     * @var integer
     */
    private $showId;

    /**
     * Constructor
     * @param integer $showid
     */
    function __construct($showid = null) {
        if($showid) {
            $this->showId = $dhowid;
        }
    }

    /**
     * Initialize the Show
     * @param unknown_type $showid
     */
    private function loadShow($showid) {
        global $db;

    }

    /**
     * Commits Show into Database
     */
    private function createShow() {
        global $db;
    }

    /**
     * returns an array of shows
     * @param integer $from
     * @param integer $to
     */
    public static function getShows($from, $to = null) {

    }

}