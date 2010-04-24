<?php
//blblb
if($_GET['key'] != "lolwar")
	die('Fagottspieler');
if(!($fp = fsockopen('localhost', 1234, $errno, $errstr)))
	echo 'Could not connect to USPS! Error number: ' . $errno . '(' . $errstr . ')';
else
{ 
	echo 'attemting to kick';
	flush();
	fwrite($fp, "list\n");
	echo 'test';
	flush();
	while(($out = fgets($fp)) != false){
		//echo $out;
		if(strrpos($out,"input.harbor") !== false){
			echo $out;
			break;
		}
		flush();
	}
	$temp = explode(" : ",$out);
	echo $temp[0];
	fwrite($fp,$temp[0].".kick\n");
	echo "done";
	flush();
	fclose($fp);
}
?>