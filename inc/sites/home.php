<?php
 if(($mysql = start_sql()) > 1)
	$page["content"] = "MySQL Fehler";

 $page["title"] = "&Uuml;bersicht";
 $page["content"] = "<div id='content'><h2>Radio freies Krautchan</h2>";

 $result = do_sql_query("SELECT id, username, rights FROM radio_auth WHERE status = 'S'", $mysql);
 if (mysql_num_rows($result)>=1) {
	$data = mysql_fetch_row($result);
	$page["content"] .= "<h3>Streaminformationen</h3>";
	$page["content"] .= "<ul>";
	$page["content"] .= "<li><b>Streamer:</b> <a href='/?u={$data[0]}'><span class='streamer'>{$data[1]}</span></a>";
	$page["content"] .= "<li><b>Song:</b> " . get_song();
	$page["content"] .= "<li><b>Zuh&ouml;rer:</b> " . get_listener();	
	$page["content"] .= "</ul>";
	$page["content"] .= "<h3>Streamlinks</h3>";
	$page["content"] .= "<ul>";
	$page["content"] .= "<li><a href='http://radio.krautchan.net:8000/radio.mp3.m3u'>MP3</a></li>";
	$page["content"] .= "<li><a href='http://radio.krautchan.net:8000/radio.ogg.m3u'>OGG</a></li>";
	$page["content"] .= "<li><a href='http://radio.krautchan.net:8000/radiohq.ogg.m3u'>OGG (High Quality)</a></li>";
	$page["content"] .= "</ul>";	
 } else {
	$page["content"] .= "<p><em>Lorem ipsum dolor sit amet, consectetur adipisici elit, sed eiusmod tempor incidunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquid ex ea commodi consequat. Quis aute iure reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint obcaecat cupiditat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</em></p>\n";
 }
 
 $result = do_sql_query("SELECT tag FROM radio ORDER BY `radio`.`time` DESC LIMIT 0, 10", $mysql);

 $page["content"] .= "</div><div id='subcontent'>\n";
 $page["content"] .= "<div class='infoblock'>\n";
 $page["content"] .= "<h3>Letzten 10 Songs</h3><ul>";
 while ($data = mysql_fetch_array($result)) {
	if ($data["tag"] == "")
		$data["tag"] = "<em>leer</em>";
	$page["content"] .= "<li>{$data["tag"]}</li>";
 }
 $page["content"] .= "</ul></div>\n";
 $page["content"] .= "<div class='infoblock'>\n";
 $page["content"] .= "<h3>Letzten 5 Streamer</h3>";
 $page["content"] .= "<ul>\n";

 $result = do_sql_query("SELECT id, username, rights, status FROM radio_auth ORDER BY `radio_auth`.`time` DESC LIMIT 0, 5", $mysql);

 while ($data = mysql_fetch_array($result)) {
	$page["content"] .= "<li><a href='/?u={$data["id"]}'>";
	if($data["rights"] != "B") {
		if($data["rights"] == "OP") 
			$page["content"] .= "<em>";
		if($data["status"] == "S")
			$page["content"] .= "<span class='streamer'>";
	} else {
		$page["content"] .= "<span class='important'>";
	}
	$page["content"] .= "{$data["username"]}";
	if ($data["rights"] == "B" || $data["status"] == "S") 
		$page["content"] .= "</span>";	 
	if ($data["rights"] == "OP")
		$page["content"] .= "</em>";
	$page["content"] .= "</a></li>\n";
 }
 
 $result = do_sql_query("SELECT * FROM radio_auth", $mysql);
 $data = mysql_num_rows($result);
 
 $page["content"] .= "</ul>\n";
 $page["content"] .= "<p><em>Insgesamt {$data} Streamer!</em></p>\n";
 $page["content"] .= "</div>\n";
 $page["content"] .= "</div>\n";
 if((stop_sql($mysql)) > 1)
	$page["content"] = "MySQL Fehler"; 
 

?>
