<?php
/**
 * Database class
 * @package system
 *
 */
class DBI {
    var $database;
    var $debugquery = true;
    private $timeSpend = 0;
    private $queryCount = 0;
    private $successfulQueries = 0;
    public function __construct ($hostname, $user, $pass, $db) {
        $this->database = new mysqli($hostname, $user, $pass, $db);
        if ($this->database->connect_error) {
            die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
        }
        $this->execute("USE $db;");
        $this->execute("SET names 'utf8';");
        $this->execute("SET time_zone = 'EUROPE/Berlin';");
    }
    /**
     * destructor
     *
     * closes databse connection
     */
    public function __destruct () {
        if ($this->database) {
            $this->database->close();
        }
    }

    public function getQueryCount () {
        return $this->queryCount;
    }

    public function getQueryTime () {
        return $this->timeSpend;
    }

    /**
     * escapes all MySQL specialchars
     * @param string $string
     * @return string
     */
    public function escape ($string) {
        return $this->database->real_escape_string($string);
    }

    public function getFoundRows(){

        $sql = "SELECT FOUND_ROWS() as rows";
        $dbres = $this->query($sql);
        if( $this->num_rows($dbres) > 0) {
            $row = $this->fetch($dbres);
            $dbres->free();
            return $row['rows'];
        }
        return false;
    }
    /**
     * Executes an SQL-Query
     * @param string $sql
     * @return ResultSet
     */
    public function query ($sql) {
        $this->queryCount++;
        $time = microtime(true);
        $result = $this->database->query($sql);
        $this->timeSpend += (microtime(true) - $time);
        if ($this->database->errno) {
            if ($this->debugquery) {
                error_log($this->database->error.' [query] '.$sql);
            } else {
                error_log($this->database->errno);
            }
            return false;
        }
        $this->successfulQueries++;
        return $result;
    }
    /**
     * executes a statement
     *
     * returns true on success
     * @param string $sql
     */
    public function execute ($sql) {
        $result = $this->query($sql);
        if ($result) {
            $this->free($result);
            return true;
        } else {
            return false;
        }
    }

    /**
     * returns the number of rows in this ResultSet
     * @param ResultSet $dbres
     * @return integer
     */
    public function num_rows ($dbres) {
        return $dbres->num_rows;
    }

    /**
     * fetches an array from a ResultSet
     * @param array $dbres
     */
    public function fetch ($dbres) {
        return $dbres->fetch_array();
    }

    /**
     * fetches an array from a ResultSet
     * @param array $dbres
     */
    public function fetch_assoc ($result) {
        return $result->fetch_assoc();
    }

    /**
     * returns the last Autoincrement value
     * @return integer
     */
    public function insert_id () {
        return $this->database->insert_id;
    }

    public function free ($result) {
        if(is_object($result)) {
            return $result->free();
        }
        return false;
    }
    /**
     * closes the database connection
     */
    public function close () {
        $this->database->close();
    }

    public function commit () {
        $this->database->commit();
        if ($this->database->errno) {
            error_log($this->database->error);
            return false;
        }
        return true;
    }

    public function rollback () {
        return $this->database->rollback();
    }

    public function getAffectedRows(){
        return $this->database->affected_rows;
    }
}
?>