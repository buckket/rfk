<?
   error_reporting(1);

   # get parameters

   $numtracks = (int)$_REQUEST['numtracks'];
   $numshows = (int)$_REQUEST['numshows'];

   # connect to database

   $db = mysqli_connect('localhost', 'radio', 'qwertz123');

   if ($db == null)
      error_response('Datenbankverbindung fehlgeschlagen',
         mysqli_connect_error());

   if (!mysqli_select_db($db, 'radio'))
      error_database($db);

   if (!mysqli_query($db, "set names 'utf8'"))
      error_database($db);

   # get current dj

   $response['current'] = get_current_dj($db);

   # get requested number of tracks

   if ($numtracks > 0)
      $response['tracks']  = get_tracks($db, $numtracks);

   # get requested number of scheduled shows

   if ($numshows > 0)
      $response['shows']  = get_scheduled_shows($db, $numshows);

   # write response

   $response['error'] = false;

   print json_encode($response);
   
   function get_scheduled_shows($db, $num)
   {
      $shows = array();

      $sql = 'select unix_timestamp(t.start), unix_timestamp(t.stop), ' .
         'a.username, a.ircname, t.sendung, t.desc from ' .
         'radio_time t, radio_auth a where t.uid = a.id ' .
         'and t.stop >= now() order by t.start asc';

      if (!$result = mysqli_query($db, $sql))
         error_database($db);

      while(($row = mysqli_fetch_row($result)))
      {
         $shows[] = array(
            'start' => (int)$row[0],
            'end' => (int)$row[1],
            'user' => $row[2],
            'ircnick' => $row[3],
            'title' => $row[4],
            'description' => $row[5]);
      }

      return($shows);
   }

   function get_listener($format) {
    $xmlfile = file_get_contents("http://88.80.19.136:8000/status4.xsl"); 
    $xml = new SimpleXMLElement($xmlfile); 
    foreach($xml->source as $source => $data) {    
      $listener[substr($data->mountpoint,1)] = $data->listener;
    }
    switch($format) {
      case 'ogg':
        $listeners = (int)$listener["radio.ogg"];
        $listeners = $listeners + (int)$listener["radiohq.ogg"];
        break;
      case 'mp3':
        $listeners = (int)$listener["radio.mp3"];
        break;
      case 'aac':
        $listeners = 0;
        break;
      default:
        $listeners = 0;
        break;
    }           
   
    return $listeners;
   
   }

   function get_tracks($db, $num)
   {
      $tracks = array();

      $sql = 'select unix_timestamp(time), tag, dj, sendung, ' .
         'listener_ogg, listener_mp3, listener_aac from radio ' .
         'order by time desc limit ' . $num;

      if (!$result = mysqli_query($db, $sql))
         error_database($db);

      while(($row = mysqli_fetch_row($result)))
      {
         $tracks[] = array(
            'time' => (int)$row[0],
            'title' => $row[1],
            'dj' => $row[2],
            'show' => $row[3],
            'listeners' => array(
               'ogg' => (int)get_listener("ogg"),
               'mp3' => (int)get_listener("mp3"),
               'aac' => (int)get_listener("aac")));
      }

      return($tracks);
   }

   function get_current_dj($db)
   {
      $sql = "select username, ircname from radio_auth where status = 'S'";

      if (!$result = mysqli_query($db, $sql))
         error_database($db);

      if (mysqli_num_rows($result) > 1)
         error_response('Mehr als ein User mit Status == Streaming',
            mysqli_num_rows($result));

      if (mysqli_num_rows($result) <= 0)
         return(NULL);

      $row = mysqli_fetch_row($result);

      return(array('user' => $row[0], 'ircnick' => $row[1]));
   }

   function error_database($db)
   {
      error_response('Datenbankfehler', mysqli_error($db));
   }

   function error_response($message, $additional)
   {
      $response = array('error' => true);

      $response['message'] = $message;
      $response['additional'] = $additional;

      print json_encode($response);
      exit(1);
   }
?>
