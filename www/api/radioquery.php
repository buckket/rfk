<?
   error_reporting(1);
   include('../../lib/common.inc.php');
   # get parameters
    //TODO WAS ZUM $_REQUEST?!
   $numtracks = (int)$_REQUEST['numtracks'];
   $numshows = (int)$_REQUEST['numshows'];

   # get current dj

   $response['current'] = get_current_dj();

   # get requested number of tracks

   if ($numtracks > 0)
      $response['tracks']  = get_tracks($numtracks);

   # get requested number of scheduled shows
   if ($numshows > 0)
      $response['shows']  = get_scheduled_shows($numshows);

   # write response

   $response['error'] = false;
   print json_encode($response);
   
   function get_scheduled_shows($num)
   {
      $shows = array();

      $sql = 'select unix_timestamp(t.start), unix_timestamp(t.stop), ' .
         'a.username, a.ircname, t.sendung, t.desc from ' .
         'radio_time t, radio_auth a where t.uid = a.id ' .
         'and t.stop >= now() order by t.start asc';
    /**
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
**/
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

   function get_tracks($num)
   {
      global $db;
      $sql = "SELECT songhistory.song, UNIX_TIMESTAMP(songhistory.start) as `time`, streamer.name as dj, shows.name as `show`,songhistory.artist,songhistory.title,mounts.shortname,l.listener FROM (SELECT sh.songid,lh.mountid,count(*) as listener from listenerhistory as lh, songhistory as sh
WHERE
(lh.connected < sh.start AND ( lh.disconnected IS NULL OR lh.disconnected BETWEEN sh.start AND sh.end))
OR
(lh.connected BETWEEN sh.start AND sh.end AND (lh.disconnected IS NULL OR lh.disconnected >= sh.end ))
OR
(lh.connected <= sh.start AND lh.disconnected >= sh.end)
group by song,mountid) as l RIGHT JOIN songhistory using (song) 
LEFT JOIN mounts using (mountid)
JOIN streamer using (userid)
JOIN shows using (showid)

ORDER BY song DESC
LIMIT $num;";
      if (!$result = $db->query($sql))
         error_database($db);

      $tracks = array();
      while($row = $db->fetch($result))
      {
        if(is_array($tracks[$row['song']])){
            $tracks[$row['song']]['listeners'][$row['shortname']] += (int)$row['listener'];
        }else{
            $temp = array();
            $temp['time'] = (int)$row['time'];
            $temp['title'] = $row['artist'].' - '.$row['title'];
            $temp['dj'] = $row['dj'];
            $temp['show'] = $row['show'];
            $temp['listeners']['aac'] = 0;
            $temp['listeners']['mp3'] = 0;
            $temp['listeners']['ogg'] = 0;
            $temp['listeners'][$row['shortname']] += (int)$row['listener'];
            $tracks[$row['song']] = $temp;
        }
         
      }
      $out = array();
      foreach($tracks as $track){
        $out[] = $track;
      }
      return($out);
   }

   function get_current_dj($db)
   {
      global $db;
      $sql = "select username from streamer where status = 'STREAMING'";

      if (!$result = $db->query($sql))
         error_database($db);

      if (mysqli_num_rows($result) > 1)
         error_response('Mehr als ein User mit Status == Streaming',
            mysqli_num_rows($result));

      if (mysqli_num_rows($result) <= 0)
         return(NULL);

      $row = mysqli_fetch_row($result);

      return(array('user' => $row[0], 'ircnick' => $row[0]));
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
