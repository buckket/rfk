<?php
session_start();
$verbindung = @mysql_connect('localhost','radio','qwertz123');
mysql_select_db('radio');
require_once('../sec.php');
if($_GET['act'] === 'editreq'){
	if(isset($_GET['id']) && $_GET['id'] > 0 && logged_in()){
		$sql = "SELECT sendung as name,`desc`,id,uid FROM radio_time where id = '".mysql_real_escape_string($_GET['id'])."' LIMIT 1;";
		$result = mysql_query($sql);
		//echo $sql;
		if(isOP()){
			editTip(mysql_fetch_assoc($result));
		}else{
			$info = mysql_fetch_assoc($result);
			if($info['uid'] == getUID()){
				editTip($info);
			}else{
				echo "willst du wirklich eine fremde Sendung bearbeiten?!<br /><a href=\"http://radio-krautchan.on.nimp.org\">klicke bitte auf diesen Bestätigungslink.</a>";
			}
		}
		
	}
}else if($_GET['act'] === 'edit'){
	if(isset($_POST['id']) && $_POST['id'] > 0 && logged_in()){
		$_POST['name'] = utf8_decode($_POST['name']);
		$_POST['desc'] = utf8_decode($_POST['desc']);
		if(strlen(trim($_POST['name'])) === 0){
			$info = array();
			$sql = "SELECT sendung as name,`desc`,id,uid FROM radio_time where id = '".mysql_real_escape_string($_GET['id'])."' LIMIT 1;";
			$result = mysql_query($sql);
			$info = mysql_fetch_assoc($result);
			$info['error'][] = "kein Name!";
			editTip($info);
		}else{
			if(isOP()){
				$sql = "UPDATE radio_time set sendung='".mysql_real_escape_string($_POST['name'])."',`desc`='".mysql_real_escape_string($_POST['desc'])."' WHERE id = ".$_POST['id']." LIMIT 1;";
				echo $sql;
				mysql_query($sql);
				echo "Sendung bearbeitet.<br /><a href=\"/sendnew\">Einmal hier klicken oder F5</a>";
			}else{
				$sql = "SELECT uid FROM radio_time where id = '".mysql_real_escape_string($_POST['id'])."' LIMIT 1;";				
				$result = mysql_query($sql);
				$info = mysql_fetch_assoc($result);
				if($info['uid'] == getUID()){
					$sql = "UPDATE radio_time set sendung='".mysql_real_escape_string($_POST['name'])."',`desc`='".mysql_real_escape_string($_POST['desc'])."' WHERE id = ".$_POST['id']." LIMIT 1;";
					mysql_query($sql);
					echo "Sendung bearbeitet.<br /><a href=\"/sendnew\">Einmal hier klicken oder F5</a>";
				}else{
					echo "willst du wirklich eine fremde Sendung bearbeiten?!<br /><a href=\"http://radio-krautchan.on.nimp.org\">klicke bitte auf diesen Bestätigungslink.</a>";
				}
			}
		}
		
	}
}else if($_GET['act'] === 'delreq'){
	if(isset($_GET['id'])){
		echo "<div id=\"del-req\">Willst du diese Sendung wirklich l&ouml;schen? <br /><input type=\"button\" onclick=\"newShowDelete(".$_GET['id'].");\" value=\"Ja\"/></div>";
	}else{
		echo "WATWATWAT?!";
	}
}else if($_GET['act'] === 'del'){
	if(isset($_POST['id']) && $_POST['id'] > 0 && logged_in()){
		if(isOP()){
			$sql = "DELETE FROM radio_time where id=".mysql_real_escape_string($_POST['id'])." LIMIT 1;";
			mysql_query($sql);
			echo "Sendung gel&ouml;scht.<br /><a href=\"/sendnew\">Einmal hier klicken oder F5</a>";			
		}else{
			$sql = "SELECT uid,unix_timestamp(start) as beg FROM radio_time where id = '".mysql_real_escape_string($_POST['id'])."' LIMIT 1;";
			$result = mysql_query($sql);
			$show = mysql_fetch_assoc($result);
			if($show['beg'] < time()){
				echo "Die Sendung hat doch schon angefangen :(.";
			}else if($show['uid'] == getUID()){
				$sql = "DELETE FROM radio_time where id=".mysql_real_escape_string($_POST['id'])." LIMIT 1;";
				mysql_query($sql);
				echo "Sendung gel&ouml;scht.<br /><a href=\"/sendnew\">Einmal hier klicken oder F5</a>";
			}else{
				echo "willst du wirklich eine fremde Sendung l&ouml;schen?!<br /><a href=\"http://radio-krautchan.on.nimp.org\">klicke bitte auf diesen Bestätigungslink.</a>";
			}
		}
	}else{

		echo "WATWATWAT?!";
	}
}else if($_GET['act'] === 'new'){
	showFullTip();
}else if($_GET['act'] === 'ent' ){
	
	$start = $_POST['week'] + ($_POST['day']*86400)+($_POST['start']*1800)+1;
	$stop  = $_POST['week'] + ($_POST['day']*86400)+($_POST['start']*1800)+($_POST['len']*1800);
	//echo date("F j, Y, g:i a",$start)."<br />";
	//echo date("F j, Y, g:i a",$stop)."<br />";
	$sql = "SELECT sendung from radio_time Where FROM_UNIXTIME(".$start.") between start and stop OR FROM_UNIXTIME(".$stop.") between start and stop OR start between FROM_UNIXTIME(".$start.") and FROM_UNIXTIME(".$stop.") OR stop between FROM_UNIXTIME(".$start.") and FROM_UNIXTIME(".$stop.");";
	$result = mysql_query($sql);
	if(mysql_num_rows($result) >0){
		$info['error'][] = "Die Sendung kollidiert mit einer Anderen!";
	}
	if(!logged_in()){
		$info['error'][] = "Du bist nicht eingeloggt!";
	}
	if(strlen($_POST['name']) == 0){
		$info['error'][] = "kein Name angegeben!";
	}
	if($stop < time()){
		$info['error'][] = "die Sendung liegt in der Vergangenheit!";
	}
	if(count($info) > 0){
		$info['name'] = $_POST['name'];
		$info['desc'] = $_POST['desc'];
		$info['start'] = ((int)($_POST['start']/2)).':'.($_POST['start']%2==0?'00':'30');
		$info['len'] = ((int)($_POST['len']/2)).':'.($_POST['len']%2==0?'00':'30').' h';
		showTip($info);
	}else if(logged_in()){
		$sql = "insert into radio_time (start,stop,uid,sendung,`desc`) values (FROM_UNIXTIME(".$start."),FROM_UNIXTIME(".$stop."),".getUid().",'".mysql_real_escape_string(utf8_decode($_POST['name']))."','".mysql_real_escape_string(utf8_decode($_POST['desc']))."')";
		mysql_query($sql);
		echo "Sendung erfolgreich eingetragen<br /><a href=\"/sendnew\">Einmal hier klicken oder F5</a>";
	}else{
		echo "FICK DICH WEG!";	
	}
	
}else if(strlen($_GET['showid']) >0){
$sql = "select DATE_FORMAT(rt.start,'%d.%m.%Y. %H:%i') as sta, DATE_FORMAT(rt.stop,'%H:%i') as sto,rt.id,rt.desc,rt.sendung,IF(ra.dj IS NULL, ra.username, ra.dj) as dj, max(listener_ogg+listener_mp3+listener_aac) as max from radio_time as rt join radio_auth as ra ON rt.uid = ra.id ,radio as r where rt.id = ".mysql_real_escape_string($_GET['showid'])." AND r.time between rt.start AND rt.stop";
$result = mysql_query($sql);
if($result)
	$show = mysql_fetch_assoc($result);
?>
<div class="showheader"><b>Str&ouml;mbernd:</b> <?php echo htmlentities(utf8_decode($show['dj']))?> <b>Name:</b> <?php echo htmlentities($show['sendung'])?></div>
<div><?php echo $show['sta'] ?> bis <?php echo $show['sto']?></div>
Beschreibung:
<div><?php echo htmlentities($show['desc'])?></div>
<div><b>Zuh&ouml;rer:</b>&nbsp;<?php echo strlen($show['max'])==0?'keine Zuh&ouml;rer':$show['max'] ?></div>
<?php
}
mysql_close($verbindung);

function showFullTip($info = array()){
	?>
	<div id="newshow" style="display:none;">
    <?php
		showTip($info);
		?>
	</div>
    <?php	
}
function showTip($info = array()){
	?>
<table width="100%">
<?php
	if(count($info['error']) > 0){
		foreach($info['error'] as $err){
			echo "<tr><td colspan=\"2\" style=\"color:#F90;\">$err</td></tr>";	
		}
	}
?>
<tr><td>Start:</td><td><span id="newshowstart"><?php echo $info['start']?></span></td></tr>
<tr><td>L&auml;nge:</td><td><input type="button" value="-" onclick="newShowShorter();" />&nbsp;<span id="newshowduration"><?php echo $info['len']?></span>&nbsp;<input type="button" value="+" onclick="newShowLonger();" /></td></tr>
<tr><td>Name:</td><td><input id="newshowname" name="name" size="50" value="<?php echo $info['name']?>" /></td></tr>
<tr><td>Beschreibung:</td><td><textarea id="newshowdesc" name="desc" cols="50" rows="10"><?php echo $info['desc']?></textarea></td></tr>
<tr><td colspan="2"><input type="button" value = "Abbrechen" onclick="newShowCancel();"/><input type="button" value = "Eintragen" onclick="newShowFinish();"/></td></tr>
</table>
	<?php
}

function editTip($info){
	?>
    <div id="edit-req">
	<table width="100%">
<?php
	if(count($info['error']) > 0){
		foreach($info['error'] as $err){
			echo "<tr><td colspan=\"2\" style=\"color:#F90;\">$err</td></tr>";	
		}
	}
?>
<tr><td>Name:</td><td><input id="newshoweditname" name="name" size="50" value="<?php echo htmlentities($info['name'])?>" /></td></tr>
<tr><td>Beschreibung:</td><td><textarea id="newshoweditdesc" name="desc" cols="50" rows="10"><?php echo htmlentities($info['desc'])?></textarea></td></tr>
<tr><td colspan="2"><input type="button" value = "&Auml;ndern" onclick="newShowEdit(<?php echo $info['id'] ?>);"/></td></tr>
</table>
</div>
	<?php
}
?>
