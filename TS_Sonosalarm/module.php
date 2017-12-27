<?
	class TS_SonosAlarm extends IPSModule
	{
		public function Create()		
    {
			//Never delete this line!
			parent::Create();
			//These lines are parsed on Symcon Startup or Instance creation
			//You cannot use variables here. Just static values.
        //You cannot use variables here. Just static values.
      $this->RegisterPropertyInteger("Sonos_ID", 0 );
      $this->RegisterPropertyInteger("Trigger", 0);
			$this->RegisterPropertyString("Pfad", "192.168.1.12/nas/");
			$this->RegisterPropertyString("Alarm1", "alarm-sirene.mp3");
      $this->RegisterPropertyInteger("AlarmVolume", 5);
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();

      $steuer_id =  $this->ReadPropertyInteger("Trigger");

$alarmskript= '<? 
$SonosId = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Sonos_ID");
$ip = IPS_GetProperty($SonosId, "IPAddress");

$pfad = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Pfad");
if (Sys_Ping($ip, 1000) == true) {
include_once("../modules/SymconSonos/Sonos/sonosAccess.php");
$sonos = new SonosAccess($ip); //Sonos ZP IPAdresse
$alarmvol = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "AlarmVolume");
$alarmdatei = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Alarm1");

///$ton="alarm";
/*
$wav = array
(
// x-file-cifs://192.168.1.11/nas/
   "alarm"   => "x-file-cifs://".$pfad.$alarmdatei,
   "bell"    => "x-file-cifs://".$pfad."alarm-sirene.mp3"
);
*/
$wav = "x-file-cifs://".$pfad.$alarmdatei;
$volume = $sonos->GetVolume();

//Speichern der Aktuellen Informationen
$oldpi = $sonos->GetPositionInfo();
$oldmi = $sonos->GetMediaInfo();
$radio=(strpos($oldmi["CurrentURI"],"x-sonosapi-stream:")>0)===false;
$oldti = $sonos->GetTransportInfo();

$sonos->SetVolume($alarmvol);
//$sonos->SetAVTransportURI($wav[$ton]);
$sonos->SetAVTransportURI($wav);
$sonos->Play();

IPS_Sleep(1000);
while ($sonos->GetTransportInfo()==1)
{
    IPS_Sleep(200); //Alle 200ms wird abgefragt
}
//Player wieder Starten
if ($radio)
{
    $sonos->SetRadio($oldmi["CurrentURI"]);
}
else
{
    $sonos->SetAVTransportURI($oldmi["CurrentUR"],$oldmi["CurrentURIMetaData"]);
}
try
{
    // Seek TRack_Nr
   $sonos->Seek("TRACK_NR",$oldpi["Track"]);
   // Seek REl_time
   $sonos->Seek("REL_TIME",$oldpi["RelTime"]);
}
catch (Exception $e)
{
}
if ($oldti==1) $sonos->Play();
$sonos->SetVolume($volume);
}
?>';
 $alarmskript_ID = $this->RegisterScript("Alarm_abspielen", "Alarm_abspielen", $alarmskript);
 IPS_SetHidden($alarmskript_ID,true);
 $this->Registerevent2($alarmskript_ID,$steuer_id); 

  $sk_id=IPS_GetObjectIDByIdent('Alarm_abspielen', $this->InstanceID);
  if ( IPS_ScriptExists($sk_id)){
      IPS_SetScriptContent ( $sk_id, $alarmskript);
  }
 

    }

		private function Registerevent2($TargetID,$Ziel_id)
		{ 
      if(!isset($_IPS))
      global $_IPS;  
      $EreignisID = @IPS_GetEventIDByName("E_true",  $TargetID);
      if ($EreignisID == true){
      if (IPS_EventExists(IPS_GetEventIDByName ( "E_true", $TargetID)))
      {
       IPS_DeleteEvent(IPS_GetEventIDByName ( "E_true", $TargetID));
      }
      }       
      $eid = IPS_CreateEvent(0);                  //Ausgelöstes Ereignis
      IPS_SetName($eid, "E_true");
//      IPS_SetEventTrigger($eid, 1, $Ziel_id);        //Bei Änderung von Variable 
      IPS_SetEventTrigger($eid, 4, $Ziel_id);        //Bei bestimmten Wert
      IPS_SetEventTriggerValue($eid, true);       
      IPS_SetParent($eid, $TargetID);         //Ereignis zuordnen
      IPS_SetEventActive($eid, true);             //Ereignis aktivieren
    }

 }   
?>
