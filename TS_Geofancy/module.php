<?

	class TS_Geofancy extends IPSModule
	{
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			//These lines are parsed on Symcon Startup or Instance creation
			//You cannot use variables here. Just static values.
			$this->RegisterPropertyString("Enter", "enter");
			$this->RegisterPropertyString("Exit", "exit");
			$this->RegisterPropertyString("Test", "test");
			$this->RegisterPropertyString("Username", "");
			$this->RegisterPropertyString("Password", "");

		}
	
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
			
			$sid = $this->RegisterScript("Hook", "Hook", "<? //Do not delete or modify.\ninclude(IPS_GetKernelDirEx().\"scripts/__ipsmodule.inc.php\");\ninclude(\"../modules/Ts_Module/TS_Geofancy/module.php\");\n(new TS_Geofancy(".$this->InstanceID."))->ProcessHookData();");
			$this->RegisterHook("/hook/TS_geofancy", $sid);

      // Start create profiles
      $this->RegisterProfileIntegerEx("Geofancy.Status", "Information", "", "", Array(
                                           Array(0, "abwesend",  "", -1),
                                           Array(1, "anwesend",  "", -1),
                                           Array(2, "test", "", -1),
      ));

		}
		
		private function RegisterHook($Hook, $TargetID)
		{
			$ids = IPS_GetInstanceListByModuleID("{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}");
			if(sizeof($ids) > 0) {
				$hooks = json_decode(IPS_GetProperty($ids[0], "Hooks"), true);
				$found = false;
				foreach($hooks as $index => $hook) {
					if($hook['Hook'] == "/hook/TS_geofancy") {
						if($hook['TargetID'] == $TargetID)
							return;
						$hooks[$index]['TargetID'] = $TargetID;
						$found = true;
					}
				}
				if(!$found) {
					$hooks[] = Array("Hook" => "/hook/TS_geofancy", "TargetID" => $TargetID);
				}
				IPS_SetProperty($ids[0], "Hooks", json_encode($hooks));
				IPS_ApplyChanges($ids[0]);
			}
		}
	
		/**
		* This function will be available automatically after the module is imported with the module control.
		* Using the custom prefix this function will be callable from PHP and JSON-RPC through:
		*
		* GEO_ProcessHookData($id);
		*
		*/
		public function ProcessHookData()
		{
			//workaround for bug
			if(!isset($_IPS))
				global $_IPS;
			if($_IPS['SENDER'] == "Execute") {
				echo "This script cannot be used this way.";
				return;
			}
			if(!isset($_POST['device']) || !isset($_POST['id']) || !isset($_POST['trigger'])) {
				IPS_LogMessage("TS_Geofancy", "Malformed data: ".print_r($_POST, true));
				return;
			}
			$trigger=(($_POST['trigger']));
      
//      if ($trigger == "test") {
  	  	  IPS_LogMessage("TS_Geofancy", "Neue Test Daten: ".print_r($_POST, true));
        
//       return;
//      }

      if ($trigger == ($this->ReadPropertyString("Exit"))    ) {
	  	  $anwesend = 0;
      }
      if ($trigger == ($this->ReadPropertyString("Enter"))   ) {
	  	  $anwesend = 1;
      }

//      if ($trigger != "test") {
//	  	  IPS_LogMessage("TS_Geofency", "Neue Daten: ".print_r($_POST, true));

 //     if ($trigger == "test") {
       if ($trigger == ($this->ReadPropertyString("Test"))  ) {
	  	  $anwesend = 2;

      }
/*
    [device] => 049D68BC-4B06-4DE6-8D5D-F69411BE922D
    [id] => 23806E01-9D38-4E1B-B4FD-1F386DA20798
    [latitude] => 51.96912
    [longitude] => 9.0148
    [timestamp] => 1439898240.085996
    [trigger] => test

    [device] => 049D68BC-4B06-4DE6-8D5D-F69411BE922D
    [id] => home
    [latitude] => 51.96894456958663
    [longitude] => 9.01457173849654
    [timestamp] => 1439899152.299569
    [trigger] => exit

    [device] => 049D68BC-4B06-4DE6-8D5D-F69411BE922D
    [id] => home
    [latitude] => 51.96894456958663
    [longitude] => 9.01457173849654
    [timestamp] => 1439899926.481014
    [trigger] => enter          

*/
			$deviceID = $this->CreateInstanceByIdent($this->InstanceID, $this->ReduceGUIDToIdent($_POST['device']), "Device"); //Dummy Modul für IOS Gerät
			SetValue($this->CreateVariableByIdent($deviceID, "Latitude", "Latitude", 2), floatval($_POST['latitude']));
			SetValue($this->CreateVariableByIdent($deviceID, "Longitude", "Longitude", 2), floatval($_POST['longitude']));
			SetValue($this->CreateVariableByIdent($deviceID, "Timestamp", "Timestamp", 1, "~UnixTimestamp"), intval($_POST['timestamp']));
      if ($trigger != "test") {
//  			SetValue($this->CreateVariableByIdent($deviceID, "Standort-".$this->ReduceGUIDToIdent($_POST['id']), "Standort-".utf8_decode($_POST['id']), 1, "Geofancy.Status"), $anwesend);  //Standort ID
    			SetValue($this->CreateVariableByIdent($deviceID, "Ort".$_POST['id'], "Ort".$_POST['id'], 1, "Geofancy.Status"), $anwesend);  //Standort ID
      }
      if ($trigger == ($this->ReadPropertyString("Exit"))   ) {
    			SetValue($this->CreateVariableByIdent($deviceID, "TimestampExit".$_POST['id'], "TimestampExit".$_POST['id'], 1, "~UnixTimestamp"), intval($_POST['timestamp']));
	    }
      if ($trigger ==  ($this->ReadPropertyString("Enter")) ) {
    			SetValue($this->CreateVariableByIdent($deviceID, "TimestampEnter".$_POST['id'], "TimestampEnter".$_POST['id'], 1, "~UnixTimestamp"), intval($_POST['timestamp']));
	    }
      if ($trigger == ($this->ReadPropertyString("Test"))   ) {
    			SetValue($this->CreateVariableByIdent($deviceID, "OrtTest", "OrtTest", 1, "Geofancy.Status"), $anwesend);  //Standort ID
   			  SetValue($this->CreateVariableByIdent($deviceID, "TimestampTest", "TimestampTest", 1, "~UnixTimestamp"), intval($_POST['timestamp']));
	    }

		}
		
		private function ReduceGUIDToIdent($guid) {
			return str_replace(Array("{", "-", "}"), "", $guid);
		}
		
		private function CreateCategoryByIdent($id, $ident, $name)
		 {
			 $cid = @IPS_GetObjectIDByIdent($ident, $id);
			 if($cid === false)
			 {
				 $cid = IPS_CreateCategory();
				 IPS_SetParent($cid, $id);
				 IPS_SetName($cid, $name);
				 IPS_SetIdent($cid, $ident);
			 }
			 return $cid;
		}
		
		private function CreateVariableByIdent($id, $ident, $name, $type, $profile = "")
		 {
			 $vid = @IPS_GetObjectIDByIdent($ident, $id);
			 if($vid === false)
			 {
				 $vid = IPS_CreateVariable($type);
				 IPS_SetParent($vid, $id);
				 IPS_SetName($vid, $name);
				 IPS_SetIdent($vid, $ident);
				 if($profile != "")
					IPS_SetVariableCustomProfile($vid, $profile);
			 }
			 return $vid;
		}
		
		private function CreateInstanceByIdent($id, $ident, $name, $moduleid = "{485D0419-BE97-4548-AA9C-C083EB82E61E}")
		 {
			 $iid = @IPS_GetObjectIDByIdent($ident, $id);
			 if($iid === false)
			 {
				 $iid = IPS_CreateInstance($moduleid);
				 IPS_SetParent($iid, $id);
				 IPS_SetName($iid, $name);
				 IPS_SetIdent($iid, $ident);
			 }
			 return $iid;
		}
    //Remove on next Symcon update
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
