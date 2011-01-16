<?php
$basePath = dirname(dirname(dirname(__FILE__)));
require_once $basePath.'/lib/common.inc.php';
require_once $basePath.'/lib/api.inc.php';
require_once $basePath.'/lib/RSS2Writer.php';

$rss = new RSS2Writer("RfK - Sendungen", "Sendungen", "http://radio.krautchan.net/",6,false);

$rss->addCategory("RSS Feed");
$out = array();
getNextShows(&$out,500);

foreach($out['shows'] as $show){
    $rss->addItem($show['showdj'].': '.$show['showname'].' ('.date('d.m.Y H:i',$show['showbegin']).' - '.date('H:i',$show['showend']).')',$show['showdescription'],'http://radio.krautchan.net/broadcasts.php');
}
echo $rss->getXML();