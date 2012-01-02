<?php
require_once(dirname(__FILE__).'/../lib/common.inc.php');
error_reporting(0); // disable error reporting

$filename = $argv[1];
if(! isset($filename)) {
    error_log("Specify a filename", 0);
    return 1;
}
else {
    fixVBR($filename);
    addMetadata($filename);
    generateTorrent($filename);
    whitelistTorrent(getTorrentHash($filename));
    return 0;
}


function fixVBR($filename) {
    
    /*
    * mp3packer
    * http://www.hydrogenaudio.org/forums/index.php?showtopic=32379
    */
    
    global $_config;
    
    $fileIn = $_config['recorddir'] . $filename;
    $fileOut = $_config['recorddir'] . substr($filename, 0, -4) . '-vbr.mp3';
    
    if (! is_file($fileIn)) {
        error_log(printf("File not found: %s", $fileIn), 0);
        return 1;
    }
        
    $command = 'mp3packer -f ' . escapeshellarg($fileIn);
    $command = escapeshellcmd($command);
    if (exec($command) && is_file($fileOut)) {
        chmod($fileOut, 0644); // mp3packer sets mode to 640 automatically
        copy($fileOut, $fileIn);
        unlink($fileOut);
        return 0;
    }
    else {
        error_log('mp3packer error', 0);
        return 1;
    }
}

function addMetadata($filename) {
    
    /*
    * id3v2
    * http://id3v2.sourceforge.net/
    */
    
    global $db, $_config;
    $fileIn = $_config['recorddir'] . $filename;
    $showId = substr($filename, 0, -4);
    
    if (! is_file($fileIn)) {
        error_log(sprintf("File not found: %s", $fileIn), 0);
        return 1;
    }
    
    $sql = "SELECT `show`, name, username
             FROM shows
             JOIN streamer USING (streamer)
             WHERE `show` = '" . $db->escape($showId) . "' ;";
    $dbres = $db->query($sql);
    if($dbres) {
        if($row = $db->fetch($dbres)) {
         $metadata = array();
         $metadata['name'] = $row['name'];
         $metadata['username'] = $row['username'];
        }
        else {
            error_log(sprintf("Show (%s) not found for addMetadata", $showId), 0);
            return 1;
        }
    }
    else {
        error_log('SQL error in addMetadata', 0);
        return 1;
    }
    $command = 'id3v2 -t ' .
        escapeshellarg($metadata['name']) . ' -a ' .
        escapeshellarg($metadata['username']) . ' -A ' .
        escapeshellarg('Radio freies Krautchan') . ' ' .
        escapeshellarg($fileIn);
    $command = escapeshellcmd($command);
    exec($command);
    return 0;
}

function generateTorrent($filename) {
    
    /*
    * File_Bittorrent2
    * http://pear.php.net/package/File_Bittorrent2
    */
    
    global $_config;
    require_once 'File/Bittorrent2/MakeTorrent.php';
    
    $fileIn = $_config['recorddir'] . $filename;
    $fileOut = $_config['torrent_dir'] . $filename . '.torrent';
    
    if (! is_file($fileIn)) {
        error_log(sprintf("File not found: %s", $fileIn), 0);
        return 1;
    }
    
    $torrentBuilder = new File_Bittorrent2_MakeTorrent($fileIn);
    $torrentBuilder->setAnnounce($_config['torrent_announce']);
    $torrentBuilder->setIsPrivate(True);
    $torrent = $torrentBuilder->buildTorrent();
        
    $torrentFile = fopen($fileOut, 'wb');
    fwrite($torrentFile, $torrent);
    fclose($torrentFile);
        
    return 0;
}

function getTorrentHash($filename) {
    
    /*
    * File_Bittorrent2
    * http://pear.php.net/package/File_Bittorrent2
    */
    
    global $_config;
    require_once 'File/Bittorrent2/Decode.php';
    
    $torrent = $_config['torrent_dir'] . $filename . '.torrent';
    
    if (! is_file($torrent)) {
        error_log(sprintf("File not found: %s", $torrent), 0);
        return 1;
    }
    $File_Bittorrent2_Decode = new File_Bittorrent2_Decode;
    $File_Bittorrent2_Decode->decodeFile($torrent);
    return $File_Bittorrent2_Decode->getInfoHash();
}

function whitelistTorrent($hash) {
    
    global $_config;
    
    $whitelist = fopen($_config['tracker_whitelist'], 'a');
    fwrite($whitelist, $hash . "\n");
    fclose($whitelist);
    
    if (is_file($_config['tracker_pidfile'])) {
        $pid = rtrim(file_get_contents($_config['tracker_pidfile']));
        if (posix_getsid($pid)) {
            $command = 'kill -1 ' . escapeshellarg($pid); // SIGHUP
            $command = escapeshellcmd($command);
            exec($command);
            return 0;
        }
        else {
            error_log('opentracker not running', 0);
            return 1;
        }
    }
    else {
        error_log(sprintf("File not found: %s", $_config['tracker_pidfile']), 0);
        return 1;
    }
}

?>
