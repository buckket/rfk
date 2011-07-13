<?php
$basePath = dirname(dirname(__FILE__));
require_once $basePath.'/lib/common.inc.php';

$chunksize = 10000;
$page = 0;
$rows = 0;
do {
    $sql = "SELECT artist, title, song, UNIX_TIMESTAMP(end) - UNIX_TIMESTAMP(begin) as length FROM songhistory LIMIT ".$page*$chunksize.",".$chunksize;
    $dbres = $db->query($sql);
    if($dbres) {
        $rows = $db->num_rows($dbres);
        while($row = $db->fetch($dbres)){
            $sql = 'UPDATE songhistory SET titleid = '.getTitleId(getArtistId($row['artist']), $row['title'], $row['length']).' WHERE song = '.$row['song'].' LIMIT 1;';
            $db->execute($sql);
            echo $row['song']."\n";
        }
    }
    $page++;
}while( $rows == $chunksize);

function getArtistId($name) {
    global $db;
    $name = trim($name);
    $sql = 'SELECT artist FROM artists WHERE name = "'.$db->escape($name).'";';
    $dbres = $db->query($sql);
    if($dbres && $artist = $db->fetch($dbres)) {
        return $artist['artist'];
    } else {
        $sql = 'INSERT INTO artists (name) VALUES ("'.$db->escape($name).'")';
        if($db->execute($sql)) {
            return $db->insert_id();
        }
    }
}

function getTitleId($artist, $title, $length) {
    global $db;
    if($length <= 0)
        $length = 0;
    $title = trim($title);
    $sql = 'SELECT title, length, lengthweight FROM titles WHERE name = "'.$db->escape($title).'";';
    $dbres = $db->query($sql);
    if($dbres && $rtitle = $db->fetch($dbres)) {
        $calclength = (($rtitle['length']*$rtitle['lengthweight'])+$length)/($rtitle['lengthweight']+1);
        $sql = 'UPDATE titles SET length = '.$calclength.', lengthweight = lengthweight+1 WHERE title = '.$rtitle['title'];
        $db->execute($sql);
        return $rtitle['title'];
    } else {
        $sql = 'INSERT INTO titles (artist,name,length,lengthweight)
                     VALUES ('.$db->escape($artist).',"'.$db->escape($title).'",'.$db->escape($length).',1)';
        if($db->execute($sql)) {
            return $db->insert_id();
        }
    }
}