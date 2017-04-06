<?

class TS_SmarterCoffee extends IPSModule {

  public function Create(){
    //Never delete this line!
    parent::Create();
    $this->ForceParent("{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}");
    $this->RegisterPropertyInteger("CupsSoll", 3);
  }

  public function ApplyChanges() {
    //Never delete this line!
    parent::ApplyChanges();
    // Start create profiles

    $this->RegisterProfileIntegerEx("Coffee_Strength", "Information", "", "", Array(
                                         Array(0, "Schwach",  "", -1),
                                         Array(1, "Mittel",  "", -1),
                                         Array(2, "Stark", "", -1),
     ));


     $this->RegisterProfileIntegerEx("Coffee_water", "Information", "", "", Array(
                                         Array(0, "leer",  "", -1),
                                         Array(1, "niedrig",  "", -1),
                                         Array(2, "halb", "", -1),
                                         Array(3, "voll", "", -1),
     ));
     $this->RegisterProfileIntegerEx("Coffee_Warmhalten", "Information", "", "", Array(
                                         Array(0, "0 Min.",  "", -1),
                                         Array(5, "5 Min.",  "", -1),
                                         Array(10, "10 Min.", "", -1),
                                         Array(15, "15 Min.", "", -1),
                                         Array(20, "20 Min.", "", -1),
                                         Array(25, "25 Min.", "", -1),
                                         Array(30, "30 Min.", "", -1),
     ));
     $this->RegisterProfileBooleanEx("Coffee_Filter", "Information", "", "", Array(
                                     Array(false, "Filter",  "", 0xFF0000),
                                     Array(true, "Bohnen",  "", 0x00FF00)
     ));
     $this->RegisterProfileBooleanEx("Coffee_Kanne", "Information", "", "", Array(
                                     Array(false, "Nein",  "", 0xFF0000),
                                     Array(true, "Ja",  "", 0x00FF00)
     ));

    $this->RegisterProfileInteger("Coffee_Cups", "Intensity", "", " Stk",   1, 12, 1);
    
    $this->RegisterVariableInteger("Cups", "Tassen", "Coffee_Cups");
    $this->RegisterVariableInteger("CupsSoll", "Tassen Soll", "Coffee_Cups");
    $this->RegisterVariableInteger("Status", "Status", "");
    $this->RegisterVariableInteger("StatusHex", "Status Hex", "");
    $this->RegisterVariableInteger("Strength", "Stärke", "Coffee_Strength");
    $this->RegisterVariableInteger("WaterLevel", "Wasserstand", "Coffee_water");
    $this->RegisterVariableInteger("ZeitHeizplatte", "Zeit Heizplatte", "Coffee_Warmhalten");
    $this->RegisterVariableBoolean("FilterBohnen", "Filter/Bohnen", "Coffee_Filter");
    $this->RegisterVariableBoolean("genugWasser", "genug Wasser?", "Coffee_Kanne");
    $this->RegisterVariableBoolean("Heizplatte", "Heizplatte", "~Switch");
    $this->RegisterVariableBoolean("Kaffeefertig", "Kaffee fertig", "Coffee_Kanne");
    $this->RegisterVariableBoolean("KanneinMaschine", "Kanne in Maschine ?", "Coffee_Kanne");
    $this->RegisterVariableBoolean("Start", "Start", "~Switch");
    
    $this->EnableAction("Strength");
    $this->EnableAction("CupsSoll");
    $this->EnableAction("Start");
    $this->EnableAction("FilterBohnen"); 
    $this->EnableAction("Heizplatte"); 
    $this->EnableAction("ZeitHeizplatte"); 
       
  }

  public function ReceiveData($JSONString) {
    $data = json_decode($JSONString);
    // Buffer decodieren und in eine Variable schreiben
    $Buffer = utf8_decode($data->Buffer);
//    $this->SendDebug('ReceiveData',$Buffer, 0);
//    $this->SendDebug('Status Tassen',$this->parseStatus($Buffer)["cups"], 0);
    SetValue($this->GetIDForIdent("Cups"),$this->parseStatus($Buffer)["cups"]);
    SetValue($this->GetIDForIdent("CupsSoll"),$this->parseStatus($Buffer)["cups_soll"]);
    SetValue($this->GetIDForIdent("Status"),$this->parseStatus($Buffer)["status"]);
    SetValue($this->GetIDForIdent("StatusHex"),$this->parseStatus($Buffer)["statushex"]);
    SetValue($this->GetIDForIdent("Strength"),$this->parseStatus($Buffer)["strength"]);
    SetValue($this->GetIDForIdent("WaterLevel"),$this->parseStatus($Buffer)["waterlevel"]);

    SetValue($this->GetIDForIdent("FilterBohnen"),$this->parseStatus($Buffer)["filter"]);
    SetValue($this->GetIDForIdent("genugWasser"),$this->parseStatus($Buffer)["genugwasser"]);
    SetValue($this->GetIDForIdent("Heizplatte"),$this->parseStatus($Buffer)["heizplatte"]);    
    SetValue($this->GetIDForIdent("Kaffeefertig"),$this->parseStatus($Buffer)["fertig"]);
    SetValue($this->GetIDForIdent("KanneinMaschine"),$this->parseStatus($Buffer)["kanne"]);
   }

  public function parseStatus($data) {
    $byte0      = ord(substr($data,0,1));// immer 0x32 - 50 Startbyte
    $result["status"] = ord(substr($data,1,1));//(carafe << 0) + (grind << 1) + (ready << 2) + (grinder << 3) + (heater << 4) + (hotplate << 6) + (working << 5) + (timer << 7))
    $result["statushex"] = dechex (ord(substr($data,1,1)) );//(carafe << 0) + (grind << 1) + (ready << 2) + (grinder << 3) + (heater << 4) + (hotplate << 6) + (working << 5) + (timer << 7))

    $result["waterlevel"]= ord(substr($data,2,1));
    $byte3      = ord(substr($data,3,1));// immer 0x00 - 0
    $result["strength"] = ord(substr($data,4,1));
    $result["cups"]     = dechex (ord(substr($data,5,1))); // passt hier nicht,44 wird angezeigt bei 2C ist es aber 12 Tassen....
    // 1te Stelle die Anzahl die gekocht werden, 2te Stelle Sollwert
	  $byte6      = ord(substr($data,6,1));// immer 0x7E - 126 Endbyte
	  
  	if ($byte0 == 50){//0x32
    	if  ($result["status"] <> 0 ) {
      	$cups =str_pad($result["cups"] , 2 ,'0', STR_PAD_LEFT);
    		$arr=str_split($cups, 1);
    		$result["cups"] = hexdec($arr[0]);
    		$result["cups_soll"] = hexdec($arr[1]);
    
    		$waterlevel = dechex($result["waterlevel"]);
    		$waterlevel =(str_pad($waterlevel, 2 ,'0', STR_PAD_LEFT));
    		$arr =str_split($waterlevel, 1);
    		$result["genugwasser"] = hexdec($arr[0]);
    		$result["waterlevel"] = (hexdec($arr[1]));
    
        $stat =(str_pad(decbin($result["status"]), 8 ,'0', STR_PAD_LEFT));
        $result["filter"] =substr($stat,6,1);
        $result["kanne"] =substr($stat,7,1);
        $result["heizplatte"] =substr($stat,1,1);
        $result["fertig"] =substr($stat,5,1);
        return $result;
      }
  	}

 }
 
	public function RequestAction($ident, $value) {

		switch($ident) {
			case "CupsSoll": 
				$this->SetCups($value);
			break;
			case "Strength": 
				$this->SetStrength($value);
			break;
			case "Start": 
				$this->SetStart($value);
			break;
			case "FilterBohnen": 
				$this->SetFilterBohnen($value);
			break;
			case "Heizplatte": 
				$this->SetHeizplatte($value);
			break;
			case "ZeitHeizplatte": 
				$this->SetZeitHeizplatte($value);
			break;

			default:
				throw new Exception("Invalid Ident");
		}
		
 
	}
  public function SetZeitHeizplatte($value) {
    SetValue($this->GetIDForIdent("ZeitHeizplatte"), $value);
 	}

  public function SetStart($value) {
    $CMD_START_BREWING = hex2bin(dechex(51));		//33
    $CMD_END = hex2bin(dechex(126));
    $CMD_STOP_BREWING =   hex2bin(dechex(52)); //34
		$SocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];

    SetValue($this->GetIDForIdent("Start"), $value);
    if ($value === true){  
  		$tassen = GetValue($this->GetIDForIdent("CupsSoll"));
      $tassen =dechex($tassen);//hex2bin("08");
  		$tassen =hex2bin(str_pad($tassen, 2 ,'0', STR_PAD_LEFT));

  		$stärke = GetValue($this->GetIDForIdent("Strength"));
  		$stärke=dechex($stärke); //hex2bin("02");
	   	$stärke =hex2bin(str_pad($stärke, 2 ,'0', STR_PAD_LEFT));

  		$grind = GetValue($this->GetIDForIdent("FilterBohnen"));
  		$grind =intval($grind);
  		$grind =dechex($grind);
  		$grind =hex2bin(str_pad($grind, 2 ,'0', STR_PAD_LEFT));

      $minuten = GetValue($this->GetIDForIdent("ZeitHeizplatte"));
      $minuten =dechex($minuten);
      $minuten =hex2bin(str_pad($minuten, 2 ,'0', STR_PAD_LEFT));
      $senden = $CMD_START_BREWING.$tassen.$stärke.$minuten.$grind.$CMD_END;    //kochen
		  $Send 		 = CSCK_SendText($SocketID, $senden);
    }
    if ($value === false){
      $senden = $CMD_STOP_BREWING.$CMD_END;    //kochen
		  $Send 		 = CSCK_SendText($SocketID, $senden);
    }  


 	}
  public function SetFilterBohnen($value) {
    $CMD_SET_GRINDER  = hex2bin(dechex(60));    
    $CMD_END = hex2bin(dechex(126));
    $senden = $CMD_SET_GRINDER.$CMD_END; 
		$SocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];
		$Send 		 = CSCK_SendText($SocketID, $senden);
		if($Send) {
			return true;
		} else {
			return false;
		}

 	}
  public function SetHeizplatte($value) {
    $CMD_END = hex2bin(dechex(126));
    $CMD_ENABLE_WARMING = hex2bin(dechex(62));		//3E
    $CMD_DISABLE_WARMING = hex2bin(dechex(74));		//4A
    $minuten = GetValue($this->GetIDForIdent("ZeitHeizplatte"));
    $minuten =dechex($minuten);
    $minuten =hex2bin(str_pad($minuten, 2 ,'0', STR_PAD_LEFT));

    if ($value === true)($heizung=$CMD_ENABLE_WARMING.$minuten.$CMD_END);   
    if ($value === false)($heizung=$CMD_DISABLE_WARMING.$CMD_END);   
    $senden = $heizung;    //Warmhalten
		$SocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];
		$Send 		 = CSCK_SendText($SocketID, $senden);
		if($Send) {
			return true;
		} else {
			return false;
		}

 	}
	
 
  public function SetCups($value) {
    $CMD_SET_CUPS     = hex2bin(dechex(54));
    $CMD_END = hex2bin(dechex(126));
    $tassen =dechex($value);
    $tassen =hex2bin(str_pad($tassen, 2 ,'0', STR_PAD_LEFT));
    $senden = $CMD_SET_CUPS.$tassen.$CMD_END;    //Tassen
		$SocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];
		$Send 		 = CSCK_SendText($SocketID, $senden);
		if($Send) {
			return true;
		} else {
			return false;
		}
	}
  public function SetStrength($value) {
    $CMD_SET_STRENGTH = hex2bin(dechex(53));		//
    $CMD_END = hex2bin(dechex(126));
    $stärke =dechex($value);
    $stärke =hex2bin(str_pad($stärke, 2 ,'0', STR_PAD_LEFT));
    $senden = $CMD_SET_STRENGTH.$stärke.$CMD_END;    //
		$SocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];
		$Send 		 = CSCK_SendText($SocketID, $senden);
		if($Send) {
			return true;
		} else {
			return false;
		}
	}

     //Remove on next Symcon update
    protected function RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize) {
        
        if(!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 0);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if($profile['ProfileType'] != 0)
            throw new Exception("Variable profile type does not match for profile ".$Name);
        }
        
        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
    }

    protected function RegisterProfileBooleanEx($Name, $Icon, $Prefix, $Suffix, $Associations) {
        if ( sizeof($Associations) === 0 ){
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[sizeof($Associations)-1][0];
        }
        
        $this->RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);
        
        foreach($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
        
    }
 
 
 
 
    protected function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize) {
        
        if(!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 1);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if($profile['ProfileType'] != 1)
            throw new Exception("Variable profile type does not match for profile ".$Name);
        }
        
        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
        
    }

    protected function RegisterProfileIntegerEx($Name, $Icon, $Prefix, $Suffix, $Associations) {
        if ( sizeof($Associations) === 0 ){
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[sizeof($Associations)-1][0];
        }
        
        $this->RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);
        
        foreach($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
        
    }



} 
?>
