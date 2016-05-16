<?

	class TS_Oel extends IPSModule
	{

		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			//These lines are parsed on Symcon Startup or Instance creation
			//You cannot use variables here. Just static values.
//			$this->RegisterPropertyString("Tankmenge", "3000");
//			$this->RegisterPropertyString("Restmenge", "100");
//      $liter_id =	$this->RegisterPropertyString("Liter_Stunde", "4.3");
			$this->RegisterPropertyInteger("ziel_id", 11219);

		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();

$sid_berechnung =			$this->RegisterScript("_berechnung", "_berechnung", '<?
$Parent           						= IPS_GetParent($_IPS[\'SELF\']);
$ParentModul								= IPS_GetObject($Parent);

// Variablen identifizieren
$Verbrauch_ID  = IPS_GetObjectIDByName(\'Verbrauch\', IPS_GetParent($_IPS[\'SELF\']));
$Verbrauch_heute_ID  = IPS_GetObjectIDByName(\'Verbrauch_heute\', IPS_GetParent($_IPS[\'SELF\']));
$zaehler_heute_ID   	= IPS_GetObjectIDByName(\'Zaehler_heute\', IPS_GetParent($_IPS[\'SELF\']));
$Zaehler_ID    		= IPS_GetObjectIDByName(\'Zaehler\', IPS_GetParent($_IPS[\'SELF\']));
$Betriebsstunden_ID 	= IPS_GetObjectIDByName(\'Betriebsstunden\', IPS_GetParent($_IPS[\'SELF\']));
$Tankinhalt_ID 	   = IPS_GetObjectIDByName(\'Tankinhalt\', IPS_GetParent($_IPS[\'SELF\']));
$brenner_ID    		= IPS_GetObjectIDByName(\'an_aus\', IPS_GetParent($_IPS[\'SELF\']));
$faktor_ID    	   	= IPS_GetObjectIDByName(\'faktor\', IPS_GetParent($_IPS[\'SELF\']));
$Restmenge_ID    	   	= IPS_GetObjectIDByName(\'Restmenge\', IPS_GetParent($_IPS[\'SELF\']));
$Tankmenge_ID    	   	= IPS_GetObjectIDByName(\'Tankmenge\', IPS_GetParent($_IPS[\'SELF\']));

//$Restmenge_str = IPS_GetProperty(IPS_GetParent($_IPS[\'SELF\']), "Restmenge");
//$Restmenge = floatval($Restmenge_str);
$Restmenge = floatval(GetValueString($Restmenge_ID));

$faktor=GetValueFloat($faktor_ID);

//$Tankmenge_str = IPS_GetProperty(IPS_GetParent($_IPS[\'SELF\']), "Tankmenge");
//$Tankmenge =  intval($Tankmenge_str);
$Tankmenge = intval(GetValueString($Tankmenge_ID));

$brennerstatus = GetValueBoolean($brenner_ID );


switch ($_IPS["SENDER"])                                     // Ursache (Absender) des Triggers ermittlen
{
  case "Variable":                                       // Brennerstatus hat sich geÃ¤ndert
    if ($brennerstatus)                                 // Brenner hat eingeschaltet
	{
     IPS_SetScriptTimer($_IPS[\'SELF\']  , 15);                  // ScriptTimer einschalten (auf 60 Sekunde setzen)
	}
    else                                                 // Brenner hat wieder ausgeschaltet
	{
     IPS_SetScriptTimer($_IPS[\'SELF\']  , 0);                  // ScriptTimer wieder lÃ¶schen

	  $zeit = GetValueInteger($Zaehler_ID  ); // Startzeit holen
	  $verbrauch = $zeit/$faktor;
	  SetValue($Verbrauch_ID , $verbrauch);

	  $zeit_heute = GetValueInteger($zaehler_heute_ID   ); // Startzeit holen
	  $verbrauch_heute = $zeit_heute/$faktor;
	  SetValue($Verbrauch_heute_ID , $verbrauch_heute);

		$hour = floor($zeit / 3600);
		$min = $zeit - ($hour*3600);
		$min = floor($min / 60);
		$sec = $zeit - ($hour*3600)-($min*60);

		//  $laufzeit_anzeige = gmdate("H:i:s", $laufzeit);
		  $laufzeit_anzeige = ($hour.":".$min.":".$sec);

		  SetValue($Betriebsstunden_ID  , $laufzeit_anzeige); // neue Brennerlaufzeit fÃ¼r die Anzeige abspeichern
		  $tank = $Tankmenge-$verbrauch;
		  $tank = $tank + $Restmenge;
		  SetValue($Tankinhalt_ID   , $tank);
   }
  break;

  case "TimerEvent":                                     // Timer hat getriggert
		$zeit = GetValueInteger($Zaehler_ID  ); // Startzeit holen
		$verbrauch = $zeit/$faktor;
		SetValue($Verbrauch_ID, $verbrauch);
 	   $zeit_heute = GetValueInteger($zaehler_heute_ID   ); // Startzeit holen
 	   $verbrauch_heute = $zeit_heute/$faktor;
	   SetValue($Verbrauch_heute_ID , $verbrauch_heute);

		$hour = floor($zeit / 3600);
		$min = $zeit - ($hour*3600);
		$min = floor($min / 60);
		$sec = $zeit - ($hour*3600)-($min*60);

		//  $laufzeit_anzeige = gmdate("H:i:s", $laufzeit);
		  $laufzeit_anzeige = ($hour.":".$min.":".$sec);

		  SetValue($Betriebsstunden_ID  , $laufzeit_anzeige); // neue Brennerlaufzeit fÃ¼r die Anzeige abspeichern
		  $tank = $Tankmenge-$verbrauch;
		  $tank = $tank + $Restmenge;
		  SetValue($Tankinhalt_ID   , $tank);
    if ($brennerstatus)                                 // Brenner hat eingeschaltet
	{
     IPS_SetScriptTimer($_IPS[\'SELF\']  , 15);                  // ScriptTimer einschalten (auf 60 Sekunde setzen)
	}
    else                                                 // Brenner hat wieder ausgeschaltet
	{
     IPS_SetScriptTimer($_IPS[\'SELF\']  , 0);                  // ScriptTimer wieder lÃ¶schen
	}
  break;
}
?>');
IPS_SetHidden($sid_berechnung,true);

$sid_Laufzeit_gesammt =			$this->RegisterScript("Laufzeit_gesammt", "Laufzeit_gesammt", '<?
$Parent           						= IPS_GetParent($_IPS[\'SELF\']);
$ParentModul								= IPS_GetObject($Parent);

// Variablen identifizieren
$zaehler_heute_ID = IPS_GetObjectIDByName(\'Zaehler_heute\', IPS_GetParent($_IPS[\'SELF\']));
$Zaehler_ID    		= IPS_GetObjectIDByName(\'Zaehler\', IPS_GetParent($_IPS[\'SELF\']));
$brenner_ID    		= IPS_GetObjectIDByName(\'an_aus\', IPS_GetParent($_IPS[\'SELF\']));

switch ($_IPS["SENDER"])                                     // Ursache (Absender) des Triggers ermittlen
{
  case "Variable":                                       // Brennerstatus hat sich geÃ¤ndert
    $brenner = GetValueBoolean($brenner_ID );
    if ($brenner)                                 // Brenner hat eingeschaltet
	{
     IPS_SetScriptTimer($_IPS[\'SELF\']  , 1);                  // ScriptTimer einschalten (auf 60 Sekunde setzen)
	}
    else                                                 // Brenner hat wieder ausgeschaltet
	{
     IPS_SetScriptTimer($_IPS[\'SELF\']  , 0);                  // ScriptTimer wieder lÃ¶schen
	}
  break;

  case "TimerEvent":                                     // Timer hat getriggert
		$laufzeit = GetValueInteger($Zaehler_ID  );   // bisherige Laufzeit
		$laufzeit = $laufzeit + 1; // Sekundentakt
		SetValue($Zaehler_ID  , $laufzeit);    // neue Brennerlaufzeit Anzeige abspeichern

		$laufzeit = GetValueInteger($zaehler_heute_ID   );   // bisherige Laufzeit
		$laufzeit = $laufzeit + 1; // Sekundentakt
		SetValue($zaehler_heute_ID   , $laufzeit);    // neue Brennerlaufzeit Anzeige abspeichern
  break;
}
?>');
IPS_SetHidden($sid_Laufzeit_gesammt,true);

$sid_Laufzeit_tag_reset=		$this->RegisterScript("Laufzeit_tag_reset", "Laufzeit_tag_reset", '<?
$Parent           						= IPS_GetParent($_IPS[\'SELF\']);
$ParentModul								= IPS_GetObject($Parent);

	// Variablen identifizieren
$Verbrauch_heute_ID    				= IPS_GetObjectIDByName(\'Verbrauch_heute\', IPS_GetParent($_IPS[\'SELF\']));
$Zaehler_heute_ID    				= IPS_GetObjectIDByName(\'Zaehler_heute\', IPS_GetParent($_IPS[\'SELF\']));

 SetValue($Verbrauch_heute_ID ,0  );
 SetValue($Zaehler_heute_ID  ,0);
?>');
IPS_SetHidden($sid_Laufzeit_tag_reset,true);

$sid_Faktor_berechnen =			$this->RegisterScript("Faktor_berechnen", "Faktor_berechnen", '<?
$faktor_ID  = IPS_GetObjectIDByName(\'faktor\', IPS_GetParent($_IPS[\'SELF\']));
$verbrauch_pro_stunde_id  = IPS_GetObjectIDByName(\'Liter_Stunde\', IPS_GetParent($_IPS[\'SELF\']));
//$eingabe_str = IPS_GetProperty(IPS_GetParent($_IPS[\'SELF\']), "Liter_Stunde");
$eingabe_str = GetValueString($verbrauch_pro_stunde_id);
$verbrauch_pro_stunde = floatval($eingabe_str);
$faktor = 3600 / $verbrauch_pro_stunde;
SetValue($faktor_ID , $faktor);
?>');
IPS_SetHidden($sid_Faktor_berechnen,true);
	
			$this->RegisterVariableFloat("Verbrauch", "Verbrauch", "~Water");
			$this->RegisterVariableInteger("Zaehler", "Zaehler", "");
      IPS_SetHidden($this->GetIDForIdent("Zaehler"),true); 
			$this->RegisterVariableInteger("Zaehler_heute", "Zaehler_heute", "");
			IPS_SetHidden($this->GetIDForIdent("Zaehler_heute"),true); 
      $this->RegisterVariableFloat("Verbrauch_heute", "Verbrauch_heute", "~Water");
			$this->RegisterVariableFloat("Tankinhalt", "Tankinhalt", "~Water");
			$this->RegisterVariableFloat("faktor", "faktor", "");
			$an_aus_id = $this->RegisterVariableBoolean("an_aus", "an_aus", "~Switch");
 			$this->RegisterVariableString("Betriebsstunden", "Betriebsstunden", "");

 			$this->RegisterVariableString("Tankmenge", "Tankmenge", "");
 			$this->RegisterVariableString("Restmenge", "Restmenge", "");
			$this->RegisterVariableFloat("faktor", "faktor", "");
      IPS_SetHidden($this->GetIDForIdent("faktor"),true); 
      $liter_id =	$this->RegisterVariableString("Liter_Stunde", "Liter_Stunde");
      IPS_SetHidden($this->GetIDForIdent("Liter_Stunde"),true); 
      $this->Registerevent_berechnung($sid_berechnung,$an_aus_id); 
      $this->Registerevent_Laufzeit_gesammt($sid_Laufzeit_gesammt,$an_aus_id); 
      $this->Registerevent_Faktor_berechnen($sid_Faktor_berechnen,$liter_id); 
      
      $ziel_id =$this->ReadPropertyInteger("ziel_id");
      $this->Registerevent_ziel_ID($an_aus_id,$ziel_id); 
      $R_reset = $this->Registerevent_tag_reset($sid_Laufzeit_tag_reset); 
	
      
		}

		private function Registerevent_berechnung($TargetID,$sid_berechnung)
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
      IPS_SetEventTrigger($eid, 1, $sid_berechnung);        //Bei Änderung von Variable mit ID 15754
      IPS_SetParent($eid, $TargetID);         //Ereignis zuordnen
      IPS_SetEventActive($eid, true);             //Ereignis aktivieren
    }	
    
		private function Registerevent_Laufzeit_gesammt($TargetID,$sid_Laufzeit_gesammt)
		{ 
      if(!isset($_IPS))
      global $_IPS;  
      $EreignisID = @IPS_GetEventIDByName("E_Laufzeit_gesammt",  $TargetID);
      if ($EreignisID == true){
      if (IPS_EventExists(IPS_GetEventIDByName ( "E_Laufzeit_gesammt", $TargetID)))
      {
       IPS_DeleteEvent(IPS_GetEventIDByName ( "E_Laufzeit_gesammt", $TargetID));
      }
      }       
      $eid = IPS_CreateEvent(0);                  //Ausgelöstes Ereignis
      IPS_SetName($eid, "E_Laufzeit_gesammt");
      IPS_SetEventTrigger($eid, 1, $sid_Laufzeit_gesammt);        //Bei Änderung von Variable mit ID 15754
      IPS_SetParent($eid, $TargetID);         //Ereignis zuordnen
      IPS_SetEventActive($eid, true);             //Ereignis aktivieren
    }	
		
    private function Registerevent_Faktor_berechnen($TargetID,$sid_Faktor_berechnen)
		{ 
      if(!isset($_IPS))
      global $_IPS;  
      $EreignisID = @IPS_GetEventIDByName("E_Faktor",  $TargetID);
      if ($EreignisID == true){
      if (IPS_EventExists(IPS_GetEventIDByName ( "E_Faktor", $TargetID)))
      {
       IPS_DeleteEvent(IPS_GetEventIDByName ( "E_Faktor", $TargetID));
      }
      }       
      $eid = IPS_CreateEvent(0);                  //Ausgelöstes Ereignis
      IPS_SetName($eid, "E_Faktor");
      IPS_SetEventTrigger($eid, 1, $sid_Faktor_berechnen);        //Bei Änderung von Variable mit ID 15754
      IPS_SetParent($eid, $TargetID);         //Ereignis zuordnen
      IPS_SetEventActive($eid, true);             //Ereignis aktivieren
    }	

    private function Registerevent_ziel_id($TargetID,$ziel_id)
		{ 
      if(!isset($_IPS))
      global $_IPS;  
      $EreignisID = @IPS_GetEventIDByName("E_status", $TargetID);
      if ($EreignisID == true){
      if (IPS_EventExists(IPS_GetEventIDByName ( "E_status", $TargetID)))
      {
       IPS_DeleteEvent(IPS_GetEventIDByName ( "E_status", $TargetID));
      }
      }       
      $eid = IPS_CreateEvent(0);                  //Ausgelöstes Ereignis
      IPS_SetName($eid, "E_status");
      IPS_SetEventTrigger($eid, 1, $ziel_id);        //Bei Änderung von Variable mit ID 15754
      IPS_SetEventScript ($eid, "SetValue(\$_IPS['TARGET'],\$_IPS['VALUE']);") ;
      IPS_SetParent($eid, $TargetID);         //Ereignis zuordnen
      IPS_SetEventActive($eid, true);             //Ereignis aktivieren
    }	
    private function Registerevent_tag_reset($TargetID)
		{ 
      if(!isset($_IPS))
      global $_IPS;  
      $EreignisID = @IPS_GetEventIDByName("E_reset", $TargetID);
      if ($EreignisID == true){
      if (IPS_EventExists(IPS_GetEventIDByName ( "E_reset", $TargetID)))
      {
       IPS_DeleteEvent(IPS_GetEventIDByName ( "E_reset", $TargetID));
      }
      }       
      $eid = IPS_CreateEvent(1);                  //Ausgelöstes Ereignis
      IPS_SetName($eid, "E_reset");
      IPS_SetEventCyclicTimeFrom($eid, 0, 0, 0);
      IPS_SetParent($eid, $TargetID);         //Ereignis zuordnen
      IPS_SetEventActive($eid, true);             //Ereignis aktivieren
    }	

	
	}

?>