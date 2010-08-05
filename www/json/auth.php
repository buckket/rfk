<?php
$verbindung = @mysql_connect('localhost','radio','qwertz123');
mysql_select_db('radio');
$post = array();
$auth = true;
foreach($_POST as $key => $value){
	$post[] = $key."=".$value;
}
$postheader = implode(',',$post);
	$sql = "insert into radio_log (time,msg) VALUES (NOW(),'".mysql_real_escape_string($postheader)."')";
mysql_query($sql);
if($_POST['user'] == 'weeee' && $_POST['pass'] == 'muh'){
	
}
if($auth){
	header('icecast-auth-user: 1');	
}
?>
