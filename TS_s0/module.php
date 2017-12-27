<?

	class TS_S0 extends IPSModule
	{

		public function Create()
		 {
			//Never delete this line!
			parent::Create();
			
			//These lines are parsed on Symcon Startup or Instance creation
			//You cannot use variables here. Just static values.
 			$this->RegisterPropertyInteger("kontakt_id", 0);
 			$this->RegisterPropertyString("Impulse", "1000");

		}
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();

			$this->RegisterVariableInteger("Zaehler", "Zaehler", "");
      IPS_SetHidden($this->GetIDForIdent("Zaehler"),true); 
			$this->RegisterVariableInteger("Zaehler_heute", "Zaehler_heute", "");
      IPS_SetHidden($this->GetIDForIdent("Zaehler_heute"),true); 
			$an_aus_id = $this->RegisterVariableBoolean("an_aus", "an_aus", "~Switch");
			$this->RegisterVariableFloat("kwh", "kwh", "~Power");
			$this->RegisterVariableFloat("kwh_tag", "kwh_tag", "~Power");

      $kontakt_id = $this->ReadPropertyInteger("kontakt_id");
      $this->Registerevent10($an_aus_id,$kontakt_id); 



			$sid2=$this->RegisterScript("Laufzeit_gesammt", "Laufzeit_gesammt", '<?
$Parent           						= IPS_GetParent($_IPS[\'SELF\']);
$ParentModul								= IPS_GetObject($Parent);

// Variablen identifizieren
$zaehler_heute_ID = IPS_GetObjectIDByName(\'Zaehler_heute\', IPS_GetParent($_IPS[\'SELF\']));
$Zaehler_ID    		= IPS_GetObjectIDByName(\'Zaehler\', IPS_GetParent($_IPS[\'SELF\']));
$an_aus_id    		= IPS_GetObjectIDByName(\'an_aus\', IPS_GetParent($_IPS[\'SELF\']));
$kwh_ID = IPS_GetObjectIDByName("kwh", IPS_GetParent($_IPS["SELF"]));
$kwh_tag_ID = IPS_GetObjectIDByName("kwh_tag", IPS_GetParent($_IPS["SELF"]));

switch ($_IPS["SENDER"])                                     // Ursache (Absender) des Triggers ermittlen
{
  case "Variable":                                       // Brennerstatus hat sich geÃ¤ndert
    $an_aus = GetValueBoolean($an_aus_id );
    if ($an_aus)                                 // Brenner hat eingeschaltet
	{
		$laufzeit = GetValueInteger($Zaehler_ID  );   // bisherige Laufzeit
		$laufzeit = $laufzeit + 1; // Sekundentakt
		SetValue($Zaehler_ID  , $laufzeit);    // neue Brennerlaufzeit Anzeige abspeichern

		$laufzeit = GetValueInteger($zaehler_heute_ID   );   // bisherige Laufzeit
		$laufzeit = $laufzeit + 1; // Sekundentakt
		SetValue($zaehler_heute_ID   , $laufzeit);    // neue Brennerlaufzeit Anzeige abspeichern
	}
    else                                                 // Brenner hat wieder ausgeschaltet
	{
    $impulse = floatval(IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Impulse"));

    $laufzeit = GetValueInteger($Zaehler_ID  );
    $kwh =$laufzeit/$impulse;
    SetValue($kwh_ID  , $kwh);    

    $laufzeit = GetValueInteger($zaehler_heute_ID  );
    $kwh =$laufzeit/$impulse;
	  SetValue($kwh_tag_ID  , $kwh);
	}
  break;
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
		private function Registerevent10($TargetID,$kontakt_id)
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
      IPS_SetEventTrigger($eid, 1, $kontakt_id);        //Bei Änderung von Variable mit ID 15754
      IPS_SetParent($eid, $TargetID);         //Ereignis zuordnen
      IPS_SetEventScript ($eid, "SetValue(\$_IPS['TARGET'],\$_IPS['VALUE']);") ;
      IPS_SetEventActive($eid, true);             //Ereignis aktivieren


    }	

	
	}

?>