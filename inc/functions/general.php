<?php
  $start = strtok(microtime(), " ") + strtok(" ");
  function get_content_vars($pageid) {
   switch($pageid) {
    case 'home':
      $return = get_include('inc/sites/home.php');
      break;
    case 'history':
      $return = get_include('inc/sites/history.php');
      break;
    case 'broadcasts':
      $return = get_include('inc/sites/broadcasts.php');
      break;
    case 'status':
      $return = get_redirect('http://88.80.19.136:8000/');
      break;
    case 'login':
      $return = get_include('inc/sites/login.php');
      break;
    case 'register':
      $return = get_include('inc/sites/register.php');
      break;
    case 'settings':
      $return = get_include('inc/sites/settings.php');
      break;
    case 'logout':
      $return = get_include('inc/sites/logout.php');
    	break;
    case 'help':
      $return = get_include('inc/sites/help.php');
      break;
    default:
      $return = get_error(404);
      break;
   }
   return $return;
  }
  
  function get_error($errid) {
    # construct error page vars ;_;
    switch($errid) {
      case 404:
        $page["title"] = "Seite wurde nicht gefunden";
        $page["content"] = "<div id='content'><p><span class='important'>Das haben wir nicht mehr. Kriegen wir auch nicht mehr rein.</span></p></div>";
        break;
      default:
        $page["title"] = "Unbekannt";
        $page["content"] = "<div id='content'><p>??????????????? -> {$errid}</p></div>";
        break;
    } 
    return $page;
  }
  
  function get_include($path) {
    if(!file_exists(DOCROOT . $path)) {
      return 404;
    }
    include($path); 
    return $page; 
  }
  /* TODO */
  function isUser() {
	if (@$_GET["debug"])
		return true;
    return false;
  }
  /* TODO */
  function get_current_login() {
	if (@$_GET["debug"])
		return "Admin";
    return "Guest";
  }
  
  /* TODO */  
  function isAdmin() {
	if (@$_GET["debug"])
		return true;
    return false;
  }
  
  function get_redirect() {
    $page["HTTP-Header"] = "Location: http://88.80.19.136:8000/";
    return $page;
  
  }
  
  function start_sql() {
    if(!$h = mysql_connect(SQL_HOST, SQL_USER, SQL_PASS))
      return 500;
    mysql_select_db(SQL_DB);
    return $h;  
  
  }
  function get_song() {
    $xmlfile = file_get_contents("http://88.80.19.136:8000/status4.xsl"); 
    $xml = new SimpleXMLElement($xmlfile); 
    foreach($xml->source as $source => $data) {    
      $title[substr($data->mountpoint,1)] = $data->title;
    }
	return $title["radiohq.ogg"];
  }
  
  function get_listener() {
    $xmlfile = file_get_contents("http://88.80.19.136:8000/status4.xsl"); 
    $xml = new SimpleXMLElement($xmlfile); 
    foreach($xml->source as $source => $data) {    
      $listener[substr($data->mountpoint,1)] = $data->listener;
    }
    $listeners = (int)$listener["radio.ogg"] + (int)$listener["radiohq.ogg"] + (int)$listener["radio.mp3"];
    if ($listeners == 0) 
	$listeners = "keine";
    return $listeners;
  }

  function do_sql_query($query, $handler) {
    $return = mysql_query($query, $handler);
    return $return;
  
  }
  
  function stop_sql($handler) {
    if(!mysql_close($handler))
        return 500;
    return 0;
  }
?>
