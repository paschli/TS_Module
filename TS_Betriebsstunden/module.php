<?

	class TS_Betriebsstunden extends IPSModule
	{

		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			//These lines are parsed on Symcon Startup or Instance creation
			//You cannot use variables here. Just static values.
 			$this->RegisterPropertyInteger("VariableID", 52331);

		}
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();

			$this->RegisterVariableInteger("Zaehler", "Zaehler", "");
      IPS_SetHidden($this->GetIDForIdent("Zaehler"),true); 
			$this->RegisterVariableInteger("Zaehler_heute", "Zaehler_heute", "");
			$this->RegisterVariableInteger("letze_Laufzeit", "letze_Laufzeit", "");

			$an_aus_id = $this->RegisterVariableBoolean("an_aus", "an_aus", "~Switch");
      $ziel_id =$this->ReadPropertyInteger("VariableID");
      $this->Registerevent_ziel_ID($an_aus_id,$ziel_id); 
//      $kontakt_id = $this->ReadPropertyInteger("VariableID");
//      $this->Registerevent10($an_aus_id,$kontakt_id); 

 			$this->RegisterVariableString("Betriebsstunden", "Betriebsstunden", "");

$sid = $this->RegisterScript("_berechnung", "_berechnung", '<?
$Parent           						= IPS_GetParent($_IPS[\'SELF\']);
$ParentModul								= IPS_GetObject($Parent);

// Variablen identifizieren
$zaehler_heute_ID   	= IPS_GetObjectIDByName(\'Zaehler_heute\', IPS_GetParent($_IPS[\'SELF\']));
$Zaehler_ID    		= IPS_GetObjectIDByName(\'Zaehler\', IPS_GetParent($_IPS[\'SELF\']));
$Betriebsstunden_ID 	= IPS_GetObjectIDByName(\'Betriebsstunden\', IPS_GetParent($_IPS[\'SELF\']));
$an_aus_ID    		= IPS_GetObjectIDByName(\'an_aus\', IPS_GetParent($_IPS[\'SELF\']));


$an_aus = GetValueBoolean($an_aus_ID);


switch ($_IPS["SENDER"])                                     // Ursache (Absender) des Triggers ermittlen
{
  case "Variable":                                       // status hat sich geändert
    if ($an_aus)                                 // eingeschaltet
	{
     IPS_SetScriptTimer($_IPS[\'SELF\']  , 15);                  // ScriptTimer einschalten (auf 60 Sekunde setzen)
	}
    else                                                 //  ausgeschaltet
	{
     IPS_SetScriptTimer($_IPS[\'SELF\']  , 0);                  // ScriptTimer wieder lÃ¶schen

	  $zeit = GetValueInteger($Zaehler_ID  ); // Startzeit holen
	  $zeit_heute = GetValueInteger($zaehler_heute_ID   ); // Startzeit holen

		$hour = floor($zeit / 3600);
		$min = $zeit - ($hour*3600);
		$min = floor($min / 60);
		$sec = $zeit - ($hour*3600)-($min*60);

		//  $laufzeit_anzeige = gmdate("H:i:s", $laufzeit);
		  $laufzeit_anzeige = ($hour.":".$min.":".$sec);

		  SetValue($Betriebsstunden_ID  , $laufzeit_anzeige); // neue Laufzeit für die Anzeige abspeichern
   }
  break;

  case "TimerEvent":                                     // Timer hat getriggert
		$zeit = GetValueInteger($Zaehler_ID  ); // Startzeit holen
 	   $zeit_heute = GetValueInteger($zaehler_heute_ID   ); // Startzeit holen

		$hour = floor($zeit / 3600);
		$min = $zeit - ($hour*3600);
		$min = floor($min / 60);
		$sec = $zeit - ($hour*3600)-($min*60);

		//  $laufzeit_anzeige = gmdate("H:i:s", $laufzeit);
		  $laufzeit_anzeige = ($hour.":".$min.":".$sec);

		  SetValue($Betriebsstunden_ID  , $laufzeit_anzeige); // neue laufzeit für die Anzeige abspeichern
    if ($an_aus)                                 // eingeschaltet
	{
     IPS_SetScriptTimer($_IPS[\'SELF\']  , 15);                  // ScriptTimer einschalten (auf 60 Sekunde setzen)
	}
    else                                                 // ausgeschaltet
	{
     IPS_SetScriptTimer($_IPS[\'SELF\']  , 0);                  // ScriptTimer wieder lÃ¶schen
	}
  break;
}
?>');
IPS_SetHidden($sid,true);
$this->Registerevent1($sid,$an_aus_id); 

			$sid2=$this->RegisterScript("Laufzeit_gesammt", "Laufzeit_gesammt", '<?
$Parent           						= IPS_GetParent($_IPS[\'SELF\']);
$ParentModul								= IPS_GetObject($Parent);

// Variablen identifizieren
$zaehler_heute_ID = IPS_GetObjectIDByName(\'Zaehler_heute\', IPS_GetParent($_IPS[\'SELF\']));
$Zaehler_ID    		= IPS_GetObjectIDByName(\'Zaehler\', IPS_GetParent($_IPS[\'SELF\']));
$an_aus_id    		= IPS_GetObjectIDByName(\'an_aus\', IPS_GetParent($_IPS[\'SELF\']));
$einschalt_id    	= IPS_GetObjectIDByName("letze_Laufzeit", IPS_GetParent($_IPS["SELF"]));

switch ($_IPS["SENDER"])                                     // Ursache (Absender) des Triggers ermittlen
{
  case "Variable":                                       // status hat sich geÃ¤ndert
    $an_aus = GetValueBoolean($an_aus_id );
    if ($an_aus)                                 //  hat eingeschaltet
	{
     SetValue($einschalt_id  , 0);
     IPS_SetScriptTimer($_IPS[\'SELF\']  , 1);                  // ScriptTimer einschalten (auf 60 Sekunde setzen)
	}
    else                                                 // hat wieder ausgeschaltet
	{
     IPS_SetScriptTimer($_IPS[\'SELF\']  , 0);                  // ScriptTimer wieder löschen
	}
  break;

  case "TimerEvent":                                     // Timer hat getriggert
		$laufzeit = GetValueInteger($Zaehler_ID  );   // bisherige Laufzeit
		$laufzeit = $laufzeit + 1; // Sekundentakt
		SetValue($Zaehler_ID  , $laufzeit);    // neue laufzeit Anzeige abspeichern

		$laufzeit = GetValueInteger($zaehler_heute_ID   );   // bisherige Laufzeit
		$laufzeit = $laufzeit + 1; // Sekundentakt
		SetValue($zaehler_heute_ID   , $laufzeit);    // neue laufzeit Anzeige abspeichern
    
    $laufzeit = GetValueInteger($einschalt_id   );   // bisherige Laufzeit
		$laufzeit = $laufzeit + 1; // Sekundentakt
		SetValue($einschalt_id   , $laufzeit);    // neue laufzeit Anzeige abspeichern

  break;
}
?>');
IPS_SetHidden($sid2,true);
$this->Registerevent2($sid2,$an_aus_id); 

		$sid3 =	$this->RegisterScript("Laufzeit_tag_reset", "Laufzeit_tag_reset", '<?
$Parent           						= IPS_GetParent($_IPS[\'SELF\']);
$ParentModul								= IPS_GetObject($Parent);

	// Variablen identifizieren
$Zaehler_heute_ID    				= IPS_GetObjectIDByName(\'Zaehler_heute\', IPS_GetParent($_IPS[\'SELF\']));
SetValue($Zaehler_heute_ID  ,0);
?>');
$this->Registerevent3($sid3,$an_aus_id); 
IPS_SetHidden($sid3,true);

		}
		private function Registerevent1($TargetID,$Ziel_id)
		{ 
      if(!isset($_IPS))
      global $_IPS;  
      $EreignisID = @IPS_GetEventIDByName("E_Laufzeit",  $TargetID);
      if ($EreignisID == true){
      if (IPS_EventExists(IPS_GetEventIDByName ( "E_Laufzeit", $TargetID)))
      {
       IPS_DeleteEvent(IPS_GetEventIDByName ( "E_Laufzeit", $TargetID));
      }
      }       
      $eid = IPS_CreateEvent(0);                  //Ausgelöstes Ereignis
      IPS_SetName($eid, "E_Laufzeit");
      IPS_SetEventTrigger($eid, 0, $Ziel_id);        //Bei Änderung von Variable 
      IPS_SetParent($eid, $TargetID);         //Ereignis zuordnen
      IPS_SetEventActive($eid, true);             //Ereignis aktivieren
    }	

		private function Registerevent2($TargetID,$Ziel_id)
		{ 
      if(!isset($_IPS))
      global $_IPS;  
      $EreignisID = @IPS_GetEventIDByName("E_Berechnung",  $TargetID);
      if ($EreignisID == true){
      if (IPS_EventExists(IPS_GetEventIDByName ( "E_Berechnung", $TargetID)))
      {
       IPS_DeleteEvent(IPS_GetEventIDByName ( "E_Berechnung", $TargetID));
      }
      }       
      $eid = IPS_CreateEvent(0);                  //Ausgelöstes Ereignis
      IPS_SetName($eid, "E_Berechnung");
      IPS_SetEventTrigger($eid, 0, $Ziel_id);        //Bei Änderung von Variable 
      IPS_SetParent($eid, $TargetID);         //Ereignis zuordnen
      IPS_SetEventActive($eid, true);             //Ereignis aktivieren
    }	

		private function Registerevent3($TargetID,$Ziel_id)
		{ 
      if(!isset($_IPS))
      global $_IPS;  
      $EreignisID = @IPS_GetEventIDByName("E_Laufzeit_tag",  $TargetID);
      if ($EreignisID == true){
      if (IPS_EventExists(IPS_GetEventIDByName ( "E_Laufzeit_tag", $TargetID)))
      {
       IPS_DeleteEvent(IPS_GetEventIDByName ( "E_Laufzeit_tag", $TargetID));
      }
      }       
      $eid = IPS_CreateEvent(1);                  //Ausgelöstes Ereignis
      IPS_SetName($eid, "E_Laufzeit_tag");
      IPS_SetParent($eid, $TargetID);         //Ereignis zuordnen
      IPS_SetEventCyclicTimeFrom($eid, 0, 0, 0);
      IPS_SetEventCyclicDateFrom($eid, 0, 0, 0);
      IPS_SetEventActive($eid, true);
    }	
		private function Registerevent_ziel_ID($TargetID,$ziel_id)
		{ 
      if(!isset($_IPS))
      global $_IPS;  
      $EreignisID = @IPS_GetEventIDByName("E_Kontakt",  $TargetID);
      if ($EreignisID == true){
      if (IPS_EventExists(IPS_GetEventIDByName ( "E_Kontakt", $TargetID)))
      {
       IPS_DeleteEvent(IPS_GetEventIDByName ( "E_Kontakt", $TargetID));
      }
      }       
      $eid = IPS_CreateEvent(0);                  //Ausgelöstes Ereignis
      IPS_SetName($eid, "E_Kontakt");
      IPS_SetEventTrigger($eid, 1, $ziel_id);        //Bei Änderung von Variable 
      IPS_SetEventScript ($eid, "SetValue(\$_IPS['TARGET'],\$_IPS['VALUE']);") ;
      IPS_SetParent($eid, $TargetID);         //Ereignis zuordnen
      IPS_SetEventActive($eid, true);             //Ereignis aktivieren
    }	

	
	}

?>