<?php
$basePath = dirname(dirname(dirname(__FILE__)));
require_once $basePath.'/lib/common.inc.php';
require_once $basePath.'/lib/api.inc.php';
require_once $basePath.'/lib/rss/FeedWriter.php';


$Feed = new FeedWriter(RSS2);

$title = 'Radio freies Krautchen';
$link = 'http://radio.krautchan.net';
$desc = 'Upcomming shows';
$Feed->setTitle($title);
$Feed->setLink($link);
$Feed->setDescription($desc);

$Feed->setImage($title,$link,$link.'/logo2.png');
$Feed->setChannelElement('language', 'de-de');
$Feed->setChannelElement('pubDate', date(DATE_RSS, time()));
$out = array();
$sql  = 'SELECT `show`, thread,UNIX_TIMESTAMP(updated) as updated,UNIX_TIMESTAMP(begin) as b,UNIX_TIMESTAMP(end) as e, name, description, type, username, streamer
                FROM shows
                JOIN streamer USING (streamer)
                WHERE begin > NOW() ';
$res = $db->query($sql);

if($res && $db->num_rows($res) > 0 ) {
    while($show = $db->fetch_assoc($res)) {
        $newItem = $Feed->createNewItem();
        $newItem->setTitle($show['username'].': '.$show['name'].' ('.date('d.m.Y H:i',$show['b']).' - '.date('H:i',$show['e']).')');
        $newItem->setLink('http://radio.krautchan.net/broadcasts.php');
        $newItem->setDate($show['updated']);
        $newItem->setDescription($show['description']);
        $newItem->addElement('author', $show['username']);

        $Feed->addItem($newItem);
    }
}

$Feed->genarateFeed();
