<?
private function CheckSocketRegVar(){
		// Prüfen / Erstellen und Verbinden des "TSKodi JSON-RPC-Socket"
		$instance = IPS_GetInstance($this->InstanceID);
		$rpcSocketModuleID = '{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}'; //Socket ID
		if($instance['ConnectionID'] == 0) { //Keine Socket Verbindung in der Instanz hinterlegt
		if($SocketID == 0) { //Keine Socket Verbindung in der Instanz hinterlegt

			$moduleIDs = IPS_GetInstanceListByModuleID($rpcSocketModuleID);
			foreach($moduleIDs as $moduleID) {
				$name = IPS_GetName($moduleID);
				if($name == "TSKodi JSON-RPC-Socket") {
					$jsonRpcSocket = IPS_GetInstance($moduleID);
					$jsonRpcSocketID = $jsonRpcSocket["InstanceID"];
					IPS_ConnectInstance($this->InstanceID, $moduleID);
				}		
			}
		

      if(!isset($jsonRpcSocketID)) {
				$jsonRpcSocketID = IPS_CreateInstance($rpcSocketModuleID);
				IPS_SetName($jsonRpcSocketID, "TSKodi JSON-RPC-Socket");
				IPS_SetProperty($jsonRpcSocketID, "Open", false);
				IPS_SetProperty($jsonRpcSocketID, "Host", "127.0.0.1");
				IPS_SetProperty($jsonRpcSocketID, "Port", "9090");
				IPS_ApplyChanges($jsonRpcSocketID); 
				IPS_ConnectInstance($this->InstanceID, $jsonRpcSocketID);
			}
		}
		
		// Prüfen / Erstellen und Verbinden der "RegisterVariable"
		$scriptsCatID 	= @IPS_GetObjectIDByIdent("TSKodi_scripts", $this->InstanceID);
		$rxScriptID 	= @IPS_GetScriptIDByName("TSKodi_Receiver", $scriptsCatID);
		
		$registerVariableModuleID = "{F3855B3C-7CD6-47CA-97AB-E66D346C037F}";
		$moduleIDs = IPS_GetInstanceListByModuleID($registerVariableModuleID);
//print_r($moduleIDs);
		foreach($moduleIDs as $moduleID) {
			$name = IPS_GetName($moduleID);
//print_r($name);
			if($name == "TSKodi RegisterVariable") {
				$registerVariable = IPS_GetInstance($moduleID);
				$registerVariableID = $registerVariable["InstanceID"];
//print_r($registerVariableID);        
				if($registerVariable['ConnectionID'] == 0) {
					IPS_ConnectInstance($registerVariableID, $jsonRpcSocketID);
					IPS_SetProperty($registerVariableID, "RXObjectID", $rxScriptID);
					IPS_SetHidden($registerVariableID, true); //Objekt verstecken
					IPS_ApplyChanges($registerVariableID);
				}				
			}
		}
		if(!isset($registerVariableID)) {
			$scriptsCatID = @IPS_GetObjectIDByIdent("TSKodi_scripts", $this->InstanceID);
			$newRegisterVariableID = IPS_CreateInstance("{F3855B3C-7CD6-47CA-97AB-E66D346C037F}");	
//print_r($newRegisterVariableID); 
			IPS_SetName($newRegisterVariableID,"TSKodi RegisterVariable");
			IPS_ConnectInstance($newRegisterVariableID, $jsonRpcSocketID);
			IPS_SetProperty($newRegisterVariableID, "RXObjectID", $rxScriptID);
			IPS_SetHidden($newRegisterVariableID, true); //Objekt verstecken
			IPS_ApplyChanges($newRegisterVariableID);
			IPS_SetParent($newRegisterVariableID, $scriptsCatID); //verschieben
		}


	}
  
 ?>