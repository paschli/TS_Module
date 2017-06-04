<?
/*
$ip = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Host");
$port = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Port");
    var_dump($ip);
//$ip="192.168.1.103";
    if (Sys_Ping($ip, 1000) == true) {

    include_once("../modules/Ts_Module/TS_Mpd/mpd.php");
$mpd = new MPD($ip, $port, '');
    $Playing = ($mpd->current_song());
 var_dump($Playing); 
	   $actuallyPlaying =$Playing[0]["Title"];
	   $actuallyPlaying =$actuallyPlaying.' - '.$Playing[0]["Artist"];


		       $nowPlaying   = GetValueString(IPS_GetObjectIDByName("nowPlaying", IPS_GetParent($_IPS["SELF"])));
    
        if ($actuallyPlaying <> $nowPlaying) {
            SetValueString(IPS_GetObjectIDByName("nowPlaying", IPS_GetParent($_IPS["SELF"])), $actuallyPlaying);
        }
}
*/
switch ($_IPS["SENDER"])                                     // Ursache (Absender) des Triggers ermittlen
{
  case "Variable":                                       // 
    $RelId = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Rel_id");
    $Rel= GetValueBoolean($RelId);
    if ($Rel == 0) {
     IPS_SetScriptTimer($_IPS["SELF"]  , 0);                  // ScriptTimer einschalten (auf 60 Sekunde setzen)
     SetValueInteger(IPS_GetObjectIDByName("Status", IPS_GetParent($_IPS["SELF"])), 3);
     SetValueString(IPS_GetObjectIDByName("nowPlaying", IPS_GetParent($_IPS["SELF"])), "");
     SetValueInteger(IPS_GetObjectIDByName("Volume", IPS_GetParent($_IPS["SELF"])), 0);
    }
    if ($Rel == 1){
     IPS_SetScriptTimer($_IPS["SELF"]  , 5);                  // ScriptTimer einschalten (auf 60 Sekunde setzen)
    }
  break;

  case "TimerEvent":                                     // Timer hat getriggert
	$ip = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Host");
	$port = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Port");
    if (Sys_Ping($ip, 1000) == true) {
		include_once("../modules/Ts_Module/TS_Mpd/mpd.php");
		$mpd = new MPD($ip, $port, '');

		$status = $mpd->update();    
//var_dump($status);
/*
04.06.2017 15:39:48 | ScriptEngine | Ergebnis für Ereignis 49041
array(14) {
  ["volume"]=>
  string(2) "25"
  ["repeat"]=>
  string(1) "0"
  ["random"]=>
  string(1) "0"
  ["single"]=>
  string(1) "0"
  ["consume"]=>
  string(1) "0"
  ["playlist"]=>
  string(2) "38"
  ["playlistlength"]=>
  string(1) "1"
  ["xfade"]=>
  string(1) "0"
  ["state"]=>
  string(4) "play"
  ["song"]=>
  string(1) "0"
  ["songid"]=>
  string(2) "80"
  ["time"]=>
  string(6) "1209:0"
  ["elapsed"]=>
  string(8) "1209.123"
  ["bitrate"]=>
  string(1) "0"
}
*/
		SetValueInteger(IPS_GetObjectIDByName("Volume", IPS_GetParent($_IPS["SELF"])), $status['volume']);
/*
        if (IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "MuteControl"))
            SetValueInteger(IPS_GetObjectIDByName("Mute", IPS_GetParent($_IPS["SELF"])), $mpd->GetMute());
*/
/*
        $this->RegisterProfileIntegerEx("MPD.Status", "Information", "", "", Array(
                                             Array(0, "Prev",  "", -1),
                                             Array(1, "Play",  "", -1),
                                             Array(2, "Pause", "", -1),
                                             Array(3, "Stop",  "", -1),
                                             Array(4, "Next",  "", -1)
*/

if ($status['state'] === "play") {
		   $state =1;
		   }
		   else {
		   $state= 3;
		   } 
            SetValueInteger(IPS_GetObjectIDByName("Status", IPS_GetParent($_IPS["SELF"])), $state);
            // Titelanzeige

            $currentStation = 0;
			$Playing = ($mpd->current_song());
//var_dump($Playing);
/*
04.06.2017 16:13:40 | ScriptEngine | Ergebnis für Ereignis 49041
array(1) {
  [0]=>
  array(12) {
    ["type"]=>
    string(4) "file"
    ["name"]=>
    string(21) "tunein:station:s96523"
    ["basename"]=>
    string(21) "tunein:station:s96523"
    ["Time"]=>
    string(1) "0"
    ["Artist"]=>
    string(38) "Radio Lippe 101.0 (Adult Contemporary)"
    ["Album"]=>
    string(38) "Radio Lippe 101.0 (Adult Contemporary)"
    ["Title"]=>
    string(11) "Radio_Lippe"
    ["Name"]=>
    string(14) "Der beste Mix."
    ["Pos"]=>
    string(1) "0"
    ["Id"]=>
    string(2) "82"
    ["X-AlbumUri"]=>
    string(21) "tunein:station:s96523"
    ["X-AlbumImage"]=>
    string(49) "http://cdn-radiotime-logos.tunein.com/s96523q.png"
  }
}
*/
			$actuallyPlaying =$Playing[0]["Title"];
			$actuallyPlaying =$actuallyPlaying.' - '.$Playing[0]["Artist"];

            if ( $state <> 1 ){
                // No title if not playing
                $actuallyPlaying = "";
            }
		$nowPlaying   = GetValueString(IPS_GetObjectIDByName("nowPlaying", IPS_GetParent($_IPS["SELF"])));
        if ($actuallyPlaying <> $nowPlaying) {
            SetValueString(IPS_GetObjectIDByName("nowPlaying", IPS_GetParent($_IPS["SELF"])), $actuallyPlaying);
        }


/*
                // start find current Radio in VariableProfile
                $Associations = IPS_GetVariableProfile("MPD.Radio")["Associations"];
    
                if(isset($Playing[0]["Artist"])){
                  foreach($Associations as $key=>$station) {
                      if( $station["Name"] == $Playing[0]["Artist"] ){
                          $currentStation = $Associations[$key]["Value"];
                      }
                  }
                }
                // end find current Radio in VariableProfile
            
            SetValueInteger(IPS_GetObjectIDByName("Radio", IPS_GetParent($_IPS["SELF"])), $currentStation);
*/
    }

  break;

}



?>
