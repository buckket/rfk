<?php
session_start();
$verbindung = @mysql_connect('localhost','radio','qwertz123');
mysql_select_db('radio');
$xml = new DOMDocument('1.0', 'UTF-8');
$roo = $xml->createElement('rss');
$roo->setAttribute('version', '2.0');
$xml->appendChild($roo);
$cha = $xml->createElement('channel');
$roo->appendChild($cha); 
switch($_GET['id']){
	case '1':
		$hea = $xml->createElement('title',
        	utf8_encode('RFK - Zuletzt gespielt'));
	    $cha->appendChild($hea);
		$hea = $xml->createElement('description',
        	utf8_encode('dumdidum'));
		$cha->appendChild($hea);
		  $hea = $xml->createElement('language',
			utf8_encode('de'));
		$cha->appendChild($hea);
		  $hea = $xml->createElement('link',
			htmlentities('http://radio.krautchan.net'));
		$cha->appendChild($hea);
		  $hea = $xml->createElement('lastBuildDate',
			utf8_encode(date("D, j M Y H:i:s ").'GMT'));
		$cha->appendChild($hea);
		$sql = "SELECT tag,UNIX_TIMESTAMP(time) as time from radio order by `time` desc LIMIT 20;";
		$result = mysql_query($sql);
		while($row = mysql_fetch_assoc($result)){
			//echo $row['tag'].'<br />';
			$itm = $xml->createElement('item');
			$dat = $xml->createElement('title');
			$dat->appendChild($xml->createTextNode(utf8_encode($row['tag'])));
			$itm->appendChild($dat);
		  	$dat = $xml->createElement('description');
			$dat->appendChild($xml->createTextNode(utf8_encode($row['tag'])));
			$itm->appendChild($dat);   
			$dat = $xml->createElement('link',
				htmlentities('http://radio.krautchan.net'));
			$itm->appendChild($dat);
		 
			$dat = $xml->createElement('pubDate',
				utf8_encode(date("D, j M Y H:i:s ",$row['time']).'GMT'));
			$itm->appendChild($dat);
		 
			$dat = $xml->createElement('guid',
				htmlentities($row['time']));
			$itm->appendChild($dat);
		    $cha->appendChild($itm);
		}
		break;
	case '2':
		$hea = $xml->createElement('title',
        	utf8_encode('RFK - Sendungen'));
	    $cha->appendChild($hea);
		$hea = $xml->createElement('description',
        	utf8_encode('WeEEeEeeeeeeeeE'));
		$cha->appendChild($hea);
		  $hea = $xml->createElement('language',
			utf8_encode('de'));
		$cha->appendChild($hea);
		  $hea = $xml->createElement('link',
			htmlentities('http://radio.krautchan.net'));
		$cha->appendChild($hea);
		  $hea = $xml->createElement('lastBuildDate',
			utf8_encode(date("D, j M Y H:i:s ").'GMT'));
		$cha->appendChild($hea);
		$sql = "Select rt.id,`update`,DATE_FORMAT(start,'%d.%m.%Y. %H:%i') as sta, DATE_FORMAT(stop,'%H:%i') as sto,rt.sendung,ra.username,`desc` from radio_time rt JOIN radio_auth as ra on rt.uid = ra.id Where stop > NOW() order by start asc";
		$result = mysql_query($sql);
		while($row = mysql_fetch_assoc($result)){
			//echo $row['tag'].'<br />';
			$itm = $xml->createElement('item');
			$dat = $xml->createElement('title');
			$title = $row['sta']." - ".$row['sto']." ".$row['username'].": ".$row['sendung'];
			$dat->appendChild($xml->createTextNode(utf8_encode($title)));
			$itm->appendChild($dat);
		  	$dat = $xml->createElement('description');
			$dat->appendChild($xml->createTextNode(utf8_encode($row['desc'])));
			$itm->appendChild($dat);   
			$dat = $xml->createElement('link',
				htmlentities('http://radio.krautchan.net'));
			$itm->appendChild($dat);
		 
			$dat = $xml->createElement('pubDate',
				utf8_encode(date("D, j M Y H:i:s ",$row['update']).'GMT'));
			$itm->appendChild($dat);
		 
			$dat = $xml->createElement('guid',
				htmlentities($row['id']));
			$itm->appendChild($dat);
		    $cha->appendChild($itm);
		}
		break;
}
$xml->formatOutput = true;
echo $xml->saveXML();
mysql_close($verbindung);
?>
