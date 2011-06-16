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
getNextShows(&$out,500);

foreach($out['shows'] as $show){
  $newItem = $Feed->createNewItem();
  $newItem->setTitle($show['showdj'].': '.$show['showname'].' ('.date('d.m.Y H:i',$show['showbegin']).' - '.date('H:i',$show['showend']).')');
  $newItem->setLink('http://radio.krautchan.net/broadcasts.php');
  $newItem->setDate($show['showbegin']);
  $newItem->setDescription($show['showdescription']);
  $newItem->addElement('author', $show['showdj']);

  $Feed->addItem($newItem);
}
$Feed->genarateFeed();
