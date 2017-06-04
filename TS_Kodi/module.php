<?

class TSKodi extends IPSModule {
	public function Create()
	{
		parent::Create();		
        $this->ForceParent("{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}"); //Client Socket
        $this->RegisterPropertyString("S1_Name", "ARD");
        $this->RegisterPropertyString("S1_Wert", "1");      
        $this->RegisterPropertyString("S2_Name", "ZDF");
        $this->RegisterPropertyString("S2_Wert", "2");      
        $this->RegisterPropertyString("S3_Name", "WDR3");
        $this->RegisterPropertyString("S3_Wert", "3");      
        $this->RegisterPropertyString("S4_Name", "RTL");
        $this->RegisterPropertyString("S4_Wert", "4");      
        $this->RegisterPropertyString("S5_Name", "SP1");
        $this->RegisterPropertyString("S5_Wert", "5");      
        $this->RegisterPropertyString("S6_Name", "ORF1");
        $this->RegisterPropertyString("S6_Wert", "6");      
        $this->RegisterPropertyString("S7_Name", "ORF2");
        $this->RegisterPropertyString("S7_Wert", "7");      
        $this->RegisterPropertyString("S8_Name", "Act");
        $this->RegisterPropertyString("S8_Wert", "8");
        $this->RegisterPropertyString("S9_Name", "Disc");
        $this->RegisterPropertyString("S9_Wert", "9");      
        $this->RegisterPropertyString("S10_Name", "Test");
        $this->RegisterPropertyString("S10_Wert", "10");      
    }
    public function ApplyChanges()
    {
        parent::ApplyChanges();
        // Start create profiles
        $this->RegisterProfileIntegerEx("tskodi.FB", "Information", "", "", Array(
                        //Array(0, "frei",  "", -1),
                        Array(1, "Power", "", -1),
/*
                        Array(2, "red", "", 16711680),
                        Array(3, "green", "", 65280 ),
                        Array(4, "yellow", "", 16776960),
*/
                        Array(5, "Select", "", -1),
                        Array(6, "Up", "", -1),
                        Array(7, "Left", "", -1),
                        Array(8, "Right", "", -1),
                        Array(9, "Down", "", -1),
                        Array(10, "Ok", "", -1),
                        Array(11, "Home", "", -1),
                        Array(12, "Vol -", "", -1),
                        Array(13, "Vol +", "", -1),
                        Array(14, "Mute", "", -1),
/*
                        Array(15, "1", "", -1),
                        Array(16, "2", "", -1),
                        Array(17, "3", "", -1),
                        Array(18, "4", "", -1),
                        Array(19, "5", "", -1),
                        Array(20, "6", "", -1),
                        Array(21, "7", "", -1),
                        Array(22, "8", "", -1),
                        Array(23, "9", "", -1),
                        Array(24, "0", "", -1),
*/
                        Array(25, "Info", "", -1),
                        Array(26, "Back", "", -1),
                        Array(27, "Fullscreen", "", -1),
 
        ));

        $this->RegisterProfileIntegerEx("kodi.Sender", "Information", "", "", Array(
                        //Array(0, "frei",  "", -1),
                        Array(1, ($this->ReadPropertyString("S1_Name")), "", -1),
                        Array(2, ($this->ReadPropertyString("S2_Name")), "", -1),
                        Array(3, ($this->ReadPropertyString("S3_Name")), "", -1),
                        Array(4, ($this->ReadPropertyString("S4_Name")), "", -1),
                        Array(5, ($this->ReadPropertyString("S5_Name")), "", -1),
                        Array(6, ($this->ReadPropertyString("S6_Name")), "", -1),
                        Array(7, ($this->ReadPropertyString("S7_Name")), "", -1),
                        Array(8, ($this->ReadPropertyString("S8_Name")), "", -1),
                        Array(9, ($this->ReadPropertyString("S9_Name")), "", -1),
                        Array(10, ($this->ReadPropertyString("S10_Name")), "", -1),

        ));		

		$this->CreateCatsVars();
		$this->CreateRXScript();
		$this->CreateStopScript();
		$this->CreateUpdateScript();
		$this->CheckSocketRegVar();
    // Start Register variables and Actions
		$Status_id  = $this->RegisterVariableInteger("FB", "FB", "tskodi.FB",10);
    $this->EnableAction("FB");

		$Station_id = $this->RegisterVariableInteger("Station", "Station", "kodi.Sender",20);
    $this->EnableAction("Station");		
    }

	public function Test()
	{
		//print_r(IPS_GetInstance($this->InstanceID));
		
	//$text = '{"jsonrpc": "2.0", "method": "Player.GetActivePlayers", "id": 1}';
		//$text = '{"id": 1, "jsonrpc": "2.0", "result": [ { "playerid": 1, "type": "video" } ]}';
		// $text = '{"jsonrpc": "2.0", "method": "Player.PlayPause", "params": { "playerid": 1 }, "id": 1}';
		//$text = '{"jsonrpc": "2.0", "method": "Player.GetItem", "params": { "properties": ["All"], "playerid": 1 }, "id": "VideoGetItem"}';
		//$text 		= '{"jsonrpc":"2.0","method":"Input.Up","id":1}';
		//$text 	= '{"jsonrpc":"2.0","method":"Input.Down","id":1}';
		//$volumeUp = '{"jsonrpc":"2.0","method":"Input.VolumeUp","id":1}';
		//$text = '{ "jsonrpc": "2.0", "method": "Player.GetItem", "params": { "properties": ["title"], "playerid":1 }, "id": 1 }';
		//$test = CSCK_SendText($id,$text);
		//$text = '{"jsonrpc": "2.0", "method": "Player.GetActivePlayers", "id": 1}';
		//$text = '{"jsonrpc":"2.0","method":"Player.GetProperties","params":{"playerid":1,"properties":["percentage"]},"id":"1"} }';
		//$channelUp 		= '{"jsonrpc":"2.0","method":"Input.Up","id":1}';
		//$test = CSCK_SendText(16791,$channelUp);
		//IPS_LogMessage('Kodi', $channelUp);
		//$text = '{"jsonrpc": "2.0", "method": "Player.GetActivePlayers", "id": 1}';
		//$test = $this->Send($text);
		//print_r(GetValue(45128));
		
		//IPS_LogMessage('KodiJSON', $test);
		//$this->GetChannelInfo();
		//$this->GetDuration();
		$this->SetActuatorsByCatIdent("TSKodi_onPlay");
	}
	
	public function IncomingData(String $data) { //Wird ausgeführt, wenn Daten empfangen werden
		$data = unserialize($data);	
		//print_r($data);        
		$this->UpdateChannelInfo($data);
		$this->UpdateDuration($data);
		$this->UpdateState($data);
		$this->UpdateVolumeVar($data);
    $this->UpdateMuteVar($data);
    $this->UpdateVolume($data);
//    $this->UpdatePlayerItemVars($data);
    
	}
	
	
	public function GetChannelInfo(){ //Anfrage Kanal und Title
//		$channelInfoJson = '{"jsonrpc": "2.0", "method": "Player.GetItem", "params": { "properties": ["title"], "playerid":1 }, "id": 1}';
		$channelInfoJson = '{"jsonrpc":"2.0","method":"Player.GetItem","params":{"playerid":1,"properties":["title","artist","albumartist","genre","year","rating","album","track","duration","comment","lyrics","musicbrainztrackid","musicbrainzartistid","musicbrainzalbumid","musicbrainzalbumartistid","playcount","fanart","director","trailer","tagline","plot","plotoutline","originaltitle","lastplayed","writer","studio","mpaa","cast","country","imdbnumber","premiered","productioncode","runtime","set","showlink","streamdetails","top250","votes","firstaired","season","episode","showtitle","thumbnail","file","resume","artistid","albumid","tvshowid","setid","watchedepisodes","disc","tag","art","genreid","displayartist","albumartistid","description","theme","mood","style","albumlabel","sorttitle","episodeguide","uniqueid","dateadded","channel","channeltype","hidden","locked","channelnumber","starttime","endtime"]},"id":1}';
		$this->Send($channelInfoJson);
	}
	public function UpdateChannelInfo(String $data){
		if(isset($data["result"]["item"]["label"]) && isset($data["result"]["item"]["title"])){
			$parent = IPS_GetParent(IPS_GetParent($_IPS['SELF']));
			
			$channel = $data["result"]["item"]["label"];
			$title = $data["result"]["item"]["title"];
			$plot = $data["result"]["item"]["plot"];
//			$plotoutline = $data["result"]["item"]["plotoutline"];
      
			SetValue(@IPS_GetObjectIDByIdent("TSKodi_channel", $parent), $channel);
			SetValue(@IPS_GetObjectIDByIdent("TSKodi_title", $parent), $title);
			SetValue(@IPS_GetObjectIDByIdent("TSKodi_plot", $parent), $plot);
      
		}
	}
	
	public function GetDuration(){
		$durationJson = '{"jsonrpc":"2.0","method":"Player.GetProperties","params":{"playerid":1,"properties":["percentage"]},"id":"1"}}';
		$this->Send($durationJson);
	}


	public function UpdateDuration(Int $data){
		if(isset($data["result"]["percentage"])){
			$parent = IPS_GetParent(IPS_GetParent($_IPS['SELF']));
			$percentage = round($data["result"]["percentage"]);
			SetValue(@IPS_GetObjectIDByIdent("TSKodi_duration", $parent), $percentage);
		}
	}

	public function GetVolume(){
		$Json = '{"jsonrpc": "2.0", "method": "Application.GetProperties", "params": {"properties": ["volume"]}, "id": 1}';
		$this->Send($Json);
	}
	public function UpdateVolume(int $data){
		if(isset($data["result"]["volume"])){
			$parent = IPS_GetParent(IPS_GetParent($_IPS['SELF']));
			$daten = $data["result"]["volume"];
			SetValue(@IPS_GetObjectIDByIdent("TSKodi_volume", $parent), $daten);
		}
	}

	public function UpdateState(Int $data){
		//Play
		if(isset($data["method"]) && $data["method"] == "Player.OnPlay"){
			SetValue($this->GetIDForIdent("TSKodi_state"), 1);
			
			$scriptsCatID 	= @IPS_GetObjectIDByIdent("TSKodi_scripts", $this->InstanceID);
			$updaterID		= @IPS_GetScriptIDByName("TSKodi_Updater", $scriptsCatID);
			IPS_SetScriptTimer($updaterID,15);
			
			$this->SetActuatorsByCatIdent("TSKodi_onPlay");
			IPS_Sleep(4);
			$this->GetChannelInfo();
			$this->GetDuration();
			
		} else if(isset($data["method"]) && $data["method"] == "Player.OnStop"){
			// Spezialfall: Beim Umschalten wird onStop aufgerufen, daher zeitverzögert prüfen
			SetValue($this->GetIDForIdent("TSKodi_state"), 0);
			
			$scriptsCatID = @IPS_GetObjectIDByIdent("TSKodi_scripts", $this->InstanceID);
			
			$stopID		= @IPS_GetScriptIDByName("TSKodi_Stop", $scriptsCatID);
			IPS_SetScriptTimer($stopID,3);
		
			$updaterID		= @IPS_GetScriptIDByName("TSKodi_Updater", $scriptsCatID);
			IPS_SetScriptTimer($updaterID,0);
						
		} else if(isset($data["method"]) && $data["method"] == "Player.OnPause"){
			SetValue($this->GetIDForIdent("TSKodi_state"), 2);
			$scriptsCatID 	= @IPS_GetObjectIDByIdent("TSKodi_scripts", $this->InstanceID);
			$updaterID		= @IPS_GetScriptIDByName("TSKodi_Updater", $scriptsCatID);
			IPS_SetScriptTimer($updaterID,0);
			
			$this->SetActuatorsByCatIdent("TSKodi_onPause");
			
		} else if(isset($data["method"]) && $data["method"] == "GUI.OnScreensaverActivated"){
			SetValue($this->GetIDForIdent("TSKodi_state"), 3);
			$scriptsCatID 	= @IPS_GetObjectIDByIdent("TSKodi_scripts", $this->InstanceID);
			$updaterID		= @IPS_GetScriptIDByName("TSKodi_Updater", $scriptsCatID);
			IPS_SetScriptTimer($updaterID,0);
			
			$this->SetActuatorsByCatIdent("TSKodi_screensaverActivated");
		}
	}
	
	
	public function SetActuatorsByCatIdent(string $ident){
		foreach(IPS_GetChildrenIDs($this->GetIDForIdent($ident)) as $actuatorLinkID) {
		//Prüfe auf Links
			if(IPS_LinkExists($actuatorLinkID)) {
				if(strpos(IPS_GetName($actuatorLinkID), "!") === false)
				{
					$reverse = false;
				} else {
					$reverse = true;
				}
				//Holt ID der Variable
				$actuatorVariableID = IPS_GetLink($actuatorLinkID)['TargetID'];
				
				if (IPS_VariableExists($actuatorVariableID)) {
					$o = IPS_GetObject($actuatorVariableID);
					$v = IPS_GetVariable($actuatorVariableID);

					$actionID = $this->GetProfileAction($v);
					
					if($reverse) {
						$value = false;
					} else {
						$value = true;
					}

					if(IPS_InstanceExists($actionID)) {
						IPS_RequestAction($actionID, $o['ObjectIdent'], $value);
					} else if(IPS_ScriptExists($actionID)) {
						IPS_RunScriptWaitEx($actionID, Array("VARIABLE" => $actuatorVariableID, "VALUE" => $value));
					} 
				}
			}
		}
	}

    public function FB(Int $_steuer)
    {

//{ "jsonrpc": "2.0", "method": "GUI.SetFullscreen", "params": {"fullscreen": true}, "id": "1"}"

        if ($_steuer === 1) ($_befehl='"System.Shutdown"');
/*
        if ($_steuer === 2) ($_befehl="RED" );
        if ($_steuer === 3) ($_befehl="GREEN" );
        if ($_steuer === 4) ($_befehl="YELLOW" );
*/
        if ($_steuer === 5) ($_befehl='"Input.Select"' );
        if ($_steuer === 25)($_befehl='"Input.Info"' );
        if ($_steuer === 26)($_befehl='"Input.Back"' );
        if ($_steuer === 6) ($_befehl='"Input.Up"' );
        if ($_steuer === 7) ($_befehl='"Input.Left"' );
        if ($_steuer === 8) ($_befehl='"Input.Right"' );
        if ($_steuer === 9) ($_befehl='"Input.Down"' );
        if ($_steuer === 10)($_befehl='"Input.Ok"' );
        if ($_steuer === 11)($_befehl='"Input.Home"' );
        if ($_steuer === 12)($_befehl='"Application.SetVolume" ,"params": {"volume": "decrement"}' );
        if ($_steuer === 13)($_befehl='"Application.SetVolume" ,"params": {"volume": "increment"}' );
        if ($_steuer === 14)($_befehl='"Application.SetMute", "params": {"mute": "toggle"}' );
        if ($_steuer === 27)($_befehl='"GUI.SetFullscreen", "params": {"fullscreen": "toggle"}' );
        
/*       
        if ($_steuer === 15)($_befehl="1" );
        if ($_steuer === 16)($_befehl="2" );
        if ($_steuer === 17)($_befehl="3" );
        if ($_steuer === 18)($_befehl="4" );
        if ($_steuer === 19)($_befehl="5" );
        if ($_steuer === 20)($_befehl="6" );
        if ($_steuer === 21)($_befehl="7" );
        if ($_steuer === 22)($_befehl="8" );
        if ($_steuer === 23)($_befehl="9" );
        if ($_steuer === 24)($_befehl="0" );
*/
			  SetValue($this->GetIDForIdent("FB"), $_steuer);	
		$sendJson = '{"jsonrpc": "2.0", "method": '.$_befehl.', "id": "1"}';
		$jsonRpcSocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];
		$kodiSend 		 = CSCK_SendText($jsonRpcSocketID, $sendJson);
  		if($kodiSend) {
			return true;
		} else {
			return false;
		}
  
}
 
    public function Station(Int $_steuer)
    {
        if ($_steuer === 1)($_befehl=$this->ReadPropertyString("S1_Wert"));   //Sport1
        if ($_steuer === 2)($_befehl=$this->ReadPropertyString("S2_Wert")); //RTL
        if ($_steuer === 3)($_befehl=$this->ReadPropertyString("S3_Wert")); //ARD
        if ($_steuer === 4)($_befehl=$this->ReadPropertyString("S4_Wert")); //ZDF
        if ($_steuer === 5)($_befehl=$this->ReadPropertyString("S5_Wert"));  //WDR Bi
        if ($_steuer === 6)($_befehl=$this->ReadPropertyString("S6_Wert")); //ORF1
        if ($_steuer === 7)($_befehl=$this->ReadPropertyString("S7_Wert")); //ORF2
        if ($_steuer === 8)($_befehl=$this->ReadPropertyString("S8_Wert")); //disc
        if ($_steuer === 9)($_befehl=$this->ReadPropertyString("S9_Wert")); //Film
        if ($_steuer === 10)($_befehl=$this->ReadPropertyString("S10_Wert")); //Action
			  SetValue($this->GetIDForIdent("Station"), $_steuer);			
    		$sendJson = '{"jsonrpc":"2.0","id":"1","method":"Player.Open","params":{"item":{"channelid": '.$_befehl.'}}}';
    		$jsonRpcSocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];
    		$kodiSend 		 = CSCK_SendText($jsonRpcSocketID, $sendJson);
    		if($kodiSend) {
    			return true;
    		} else {
    			return false;
    		}
    }

	public function RequestAction($ident, $value) {
		
		switch($ident) {
      case "FB":
          $result = $this->FB($value);
          break;
      case "Station":
          $result = $this->Station($value);
          break;

			case "TSKodi_on": 
				$this->SetOn();
			break;
			case "TSKodi_off": 
				$this->SetOff();
			break;
			case "TSKodi_channelUp": 
				$this->SendKey("Up");
			break;
			case "TSKodi_channelDown": 
				$this->SendKey("Down");
			break;
			case "TSKodi_volume": 
				$this->SetVolume($value);
			break;
			case "TSKodi_mute": 
				$this->SetMute($value);
			break;
			case "TSKodi_record": 
				$this->SetRecord();
			break;
			case "TSKodi_playPause": 
				$this->SetPlayPause();
			break;
			case "TSKodi_stopp": 
				$this->SetStopp();
			break;
			default:
				throw new Exception("Invalid Ident");
		}
		
 
	}
	
	public function Send(String $sendJson){
		$jsonRpcSocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];
//    $fehler = (IPS_GetInstance($this->InstanceID)['InstanceStatus']);
//    print_r ($fehler.chr(10));
		$kodiSend 		 = CSCK_SendText($jsonRpcSocketID, $sendJson);
//    print_r ($kodiSend.chr(10));

		if($kodiSend) {
			return true;
		} else {
			return false;
		}
	}	
	
	public function SetOn(){
		$sendJson = '{"jsonrpc":"2.0","method":"Addons.ExecuteAddon","params":{"addonid":"script.json-cec","params":{"command":"activate"}},"id":1}';
		$jsonRpcSocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];
		$kodiSend 		 = CSCK_SendText($jsonRpcSocketID, $sendJson);
		if($kodiSend) {
			return true;
		} else {
			return false;
		}
	}
	
	public function SetOff(){
		$sendJson = '{"jsonrpc": "2.0", "method": "System.Shutdown", "id": 1}';
		$jsonRpcSocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];
		$kodiSend 		 = CSCK_SendText($jsonRpcSocketID, $sendJson);
		if($kodiSend) {
			return true;
		} else {
			return false;
		}
	}
	
	public function SendKey(Int $key){
		$sendJson = '{"jsonrpc":"2.0","method":"Input.'.$key.'","id":1}';
		$jsonRpcSocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];
		$kodiSend 		 = CSCK_SendText($jsonRpcSocketID, $sendJson);
		if($kodiSend) {
			return true;
		} else {
			return false;
		}
	}
	
	public function SetVolume(Int $value){
		$sendJson = '{"jsonrpc": "2.0", "method": "Application.SetVolume", "params": { "volume": '.$value.'}, "id": 1}';
		$jsonRpcSocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];
		$kodiSend 		 = CSCK_SendText($jsonRpcSocketID, $sendJson);
		if($kodiSend) {
			return true;
		} else {
			return false;
		}
	}
	
	public function SetMute(Bool $value){
		$sendJson = '{"jsonrpc": "2.0", "method": "Application.SetMute", "params": {"mute": "toggle"}, "id": "1"}';
		//$value = '".$value."';
    $jsonRpcSocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];
		$kodiSend 		 = CSCK_SendText($jsonRpcSocketID, $sendJson);
		if($kodiSend) {
			return true;
		} else {
			return false;
		}
	}
	
	public function SetRecord(){
		$sendJson = '{"jsonrpc": "2.0", "method": "PVR.Record", "params": {"record": "toggle", "channel": "current"}, "id": "1"}';
    $jsonRpcSocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];
		$kodiSend 		 = CSCK_SendText($jsonRpcSocketID, $sendJson);
		if($kodiSend) {
			return true;
		} else {
			return false;
		}
	}
	
	public function SetPlayPause(){
		$sendJson = '{"jsonrpc": "2.0", "method": "Player.PlayPause", "params": { "playerid": 1 }, "id": 1}';
    $jsonRpcSocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];
		$kodiSend 		 = CSCK_SendText($jsonRpcSocketID, $sendJson);
		if($kodiSend) {
			return true;
		} else {
			return false;
		}
	}
	
	public function SetStopp(){
		$sendJson = '{"jsonrpc": "2.0", "method": "Player.Stop", "params": { "playerid": 1 }, "id": 1}';
    $jsonRpcSocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];
		$kodiSend 		 = CSCK_SendText($jsonRpcSocketID, $sendJson);
		if($kodiSend) {
			return true;
		} else {
			return false;
		}
	}
	
	// Update Funktionen (Prüft Rückgabewerte)
	public function UpdateVolumeVar(Int $data){
		if(isset($data["params"]["data"]["volume"])){
			$volume 	= $data["params"]["data"]["volume"];
			SetValue($this->GetIDForIdent("TSKodi_volume"), $volume);			
		}
	}
	public function UpdateMuteVar(Bool $data){
		if(isset($data["params"]["data"]["muted"])){
			$mute 	= $data["params"]["data"]["muted"];
			SetValue($this->GetIDForIdent("TSKodi_mute"), $mute);			
		}
	}
	
	public function UpdatePlayerItemVars(String $data){
		if(isset($data["result"]["item"]["label"]) && isset($data["result"]["item"]["title"])){
			$parent = IPS_GetParent(IPS_GetParent($_IPS['SELF']));
			
			$channel = $data["result"]["item"]["label"];
			$title = $data["result"]["item"]["title"];
			
			SetValue(@IPS_GetObjectIDByIdent("TSKodi_channel", $parent), $channel);
			SetValue(@IPS_GetObjectIDByIdent("TSKodi_title", $parent), $title);
		}
	}
	
	private function GetProfileAction(String $variable) 
	{
		if($variable['VariableCustomAction'] != ""){
			return $variable['VariableCustomAction'];
		} else {
			return $variable['VariableAction'];
		}
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
			 {
				IPS_SetVariableCustomProfile($vid, $profile);
			 }
		 }
		 return $vid;
	}
	
	private function CreateCatsVars(){ 
		$scriptsCatID = $this->CreateCategoryByIdent($this->InstanceID, "TSKodi_scripts", "Scripte"); //Kategorie Scripte
		IPS_SetHidden($scriptsCatID, true);
		IPS_SetPosition($scriptsCatID,0);
/*		
		$onPlayCatID = $this->CreateCategoryByIdent($this->InstanceID, "TSKodi_onPlay", "Play"); //Kategorie Scripte
		IPS_SetHidden($onPlayCatID, true);
		IPS_SetPosition($onPlayCatID,1);
		
		$onPauseCatID = $this->CreateCategoryByIdent($this->InstanceID, "TSKodi_onPause", "Pause"); //Kategorie Scripte
		IPS_SetHidden($onPauseCatID, true);
		IPS_SetPosition($onPauseCatID,2);
		
		$onStopCatID = $this->CreateCategoryByIdent($this->InstanceID, "TSKodi_onStop", "Stop"); //Kategorie Scripte
		IPS_SetHidden($onStopCatID, true);
		IPS_SetPosition($onStopCatID,3);
		
		$screensaverActivatedCatID 	= $this->CreateCategoryByIdent($this->InstanceID, "TSKodi_screensaverActivated", "Screensaver"); //Kategorie Scripte
		IPS_SetHidden($screensaverActivatedCatID, true);
		IPS_SetPosition($screensaverActivatedCatID,4);
*/		
		$channelID = $this->CreateVariableByIdent($this->InstanceID, "TSKodi_channel", "Kanal", 3, "");
		IPS_SetPosition($channelID,5);
		
		$titleID = $this->CreateVariableByIdent($this->InstanceID, "TSKodi_title", "Titel", 3, "");
		IPS_SetPosition($titleID,6);

		$plotID = $this->CreateVariableByIdent($this->InstanceID, "TSKodi_plot", "Plot", 3, "~HTMLBox");
		IPS_SetPosition($plotID,7);
		
		$durationID = $this->CreateVariableByIdent($this->InstanceID, "TSKodi_duration", "Fortschritt", 1, "~Valve");
		$this->EnableAction("TSKodi_duration");
		IPS_SetPosition($durationID,8);
		
		if(!IPS_VariableProfileExists("TSKodi_State")){
			IPS_CreateVariableProfile("TSKodi_State", 1);
			IPS_SetVariableProfileAssociation("TSKodi_State", 0, "Stop", "Close", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("TSKodi_State", 1, "Play", "Script", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("TSKodi_State", 2, "Pause", "Hourglass", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("TSKodi_State", 3, "Screensaver", "Sleep", 0xFFFFFF);
			IPS_SetVariableProfileValues("TSKodi_State", 0, 3, 1);
		}
		
		$stateID = $this->CreateVariableByIdent($this->InstanceID, "TSKodi_state", "Status", 1, "TSKodi_State");
		IPS_SetPosition($stateID,9);
		
		 
		$this->CreateVariableByIdent($this->InstanceID, "TSKodi_on", "Einschalten", 0, "~Switch");
		$this->EnableAction("TSKodi_on");
		$this->CreateVariableByIdent($this->InstanceID, "TSKodi_off", "Ausschalten", 0, "~Switch");
		$this->EnableAction("TSKodi_off");
		$this->CreateVariableByIdent($this->InstanceID, "TSKodi_channelUp", "Kanal hoch", 0, "~Switch");
		$this->EnableAction("TSKodi_channelUp");
		$this->CreateVariableByIdent($this->InstanceID, "TSKodi_channelDown", "Kanal runter", 0, "~Switch");
		$this->EnableAction("TSKodi_channelDown");

		$this->CreateVariableByIdent($this->InstanceID, "TSKodi_volume", "Lautstärke", 1, "~Valve");
		$this->EnableAction("TSKodi_volume");
		
		$this->CreateVariableByIdent($this->InstanceID, "TSKodi_mute", "Mute", 0, "~Switch");
		$this->EnableAction("TSKodi_mute");
//		$this->CreateVariableByIdent($this->InstanceID, "TSKodi_record", "Aufnahme", 0, "~Switch");
//		$this->EnableAction("TSKodi_record");
		
		$this->CreateVariableByIdent($this->InstanceID, "TSKodi_playPause", "Pause", 0, "~Switch");
		$this->EnableAction("TSKodi_playPause");
		
		$this->CreateVariableByIdent($this->InstanceID, "TSKodi_stopp", "Stop", 0, "~Switch");
		$this->EnableAction("TSKodi_stopp");
		
	}
	
	private function CreateRXScript(){
		// Receiver-Script erstellen
		$scriptsCatID = @IPS_GetObjectIDByIdent("TSKodi_scripts", $this->InstanceID);
		
		$script  = '<?'."\n";
		$script .= '	if ($_IPS[\'SENDER\'] == \'RegisterVariable\'){'."\n";
		$script .= '		$jsonData = RegVar_GetBuffer($_IPS[\'INSTANCE\']);'."\n";
		$script .= '		$jsonData = $_IPS[\'VALUE\'];'."\n";
		$script .= '		if($jsonData){'."\n";
		$script .= '			$data = json_decode($jsonData,true);'."\n";
		$script .= '			//print_r($data);'."\n";
		$script .= '			if($data){'."\n";
		$script .= '				TSKodi_IncomingData('.$this->InstanceID.', serialize($data));'."\n";
		$script .= '			}'."\n";
		$script .= '		}'."\n";
		$script .= '	}'."\n";
		$script .= '	RegVar_SetBuffer($_IPS[\'INSTANCE\'], $jsonData);'."\n";
		$script .= '	unset($data);'."\n";
		$script .= '	unset($jsonData);'."\n";
		$script .= '?>';
		
		if(!@IPS_GetScriptIDByName("TSKodi_Receiver", $scriptsCatID)){
			$scriptID = IPS_CreateScript(0);
			IPS_SetName($scriptID, "TSKodi_Receiver");
			IPS_SetScriptContent($scriptID, $script);
			IPS_SetParent($scriptID, $scriptsCatID);
		}
	}
	
	private function CreateUpdateScript(){
		// Receiver-Script erstellen
		$scriptsCatID = @IPS_GetObjectIDByIdent("TSKodi_scripts", $this->InstanceID);
		
		$script  = '<?'."\n";
		$script .= '$id1 = IPS_GetParent($_IPS["SELF"]);'."\n";
		$script .= '$id2 = IPS_GetParent($id1);'."\n";
		$script .= '$id3 = IPS_GetInstance($id2)["ConnectionID"];'."\n";
		$script .= '$ip = IPS_GetProperty($id3,"Host");'."\n";
		$script .= 'if (Sys_Ping($ip,1000) == true ){'."\n";
		$script .= '   IPS_SetHidden($id2, false);'."\n";
		$script .= '	TSKodi_GetChannelInfo('.$this->InstanceID.');'."\n";
		$script .= '	TSKodi_GetDuration('.$this->InstanceID.');'."\n";
		$script .= '} else {'."\n";
		$script .= '   // IPS_SetHidden($id2, true);'."\n";
		$script .= '}'."\n";
		$script .= '?>';
		
		if(!@IPS_GetScriptIDByName("TSKodi_Updater", $scriptsCatID)){
			$scriptID = IPS_CreateScript(0);
			IPS_SetName($scriptID, "TSKodi_Updater");
			IPS_SetScriptContent($scriptID, $script);
			//IPS_SetScriptTimer($scriptID, 10);
			IPS_SetParent($scriptID, $scriptsCatID);
		}
	}
	
	private function CreateStopScript(){
		$scriptsCatID = @IPS_GetObjectIDByIdent("TSKodi_scripts", $this->InstanceID);
		
		if(!@IPS_GetScriptIDByName("TSKodi_Stop", $scriptsCatID)){
			$scriptID = IPS_CreateScript(0);
		
			$script  = '<?'."\n";
			$script .= '	if(GetValue(@IPS_GetObjectIDByIdent("TSKodi_state", '.$this->InstanceID.')) == 0){'."\n";
			$script .= '		SetValue(@IPS_GetObjectIDByIdent("TSKodi_channel", '.$this->InstanceID.'), "");'."\n";
			$script .= '		SetValue(@IPS_GetObjectIDByIdent("TSKodi_title", '.$this->InstanceID.'), "");'."\n";
			$script .= '		SetValue(@IPS_GetObjectIDByIdent("TSKodi_duration", '.$this->InstanceID.'), 0);'."\n";
			$script .= '		SetValue(@IPS_GetObjectIDByIdent("TSKodi_plot", '.$this->InstanceID.'), "");'."\n";
			$script .= '		TSKodi_SetActuatorsByCatIdent('.$this->InstanceID.', "TSKodi_stopp");'."\n";
			$script .= '	}'."\n";
			$script .= '	IPS_SetScriptTimer('.$scriptID.', 0);'."\n";
			$script .= '?>';
			
			IPS_SetName($scriptID, "TSKodi_Stop");
			IPS_SetScriptContent($scriptID, $script);
			IPS_SetParent($scriptID, $scriptsCatID);
		}
	}
	
	private function CheckSocketRegVar(){
/*
    $SocketID = IPS_GetInstance($this->InstanceID)["ConnectionID"];
		// Prüfen / Erstellen und Verbinden der "RegisterVariable"
		$scriptsCatID = @IPS_GetObjectIDByIdent("TSKodi_scripts", $this->InstanceID);
		$rxScriptID 	= @IPS_GetScriptIDByName("TSKodi_Receiver", $scriptsCatID);
		
		$registerVariableModuleID = "{F3855B3C-7CD6-47CA-97AB-E66D346C037F}";
		$moduleIDs = IPS_GetInstanceListByModuleID($registerVariableModuleID);
		foreach($moduleIDs as $moduleID) {
			$name = IPS_GetName($moduleID);
			if($name == "TSKodi RegisterVariable") {
				$registerVariable = IPS_GetInstance($moduleID);
				$registerVariableID = $registerVariable["InstanceID"];
				if($registerVariable['ConnectionID'] == 0) {
					IPS_ConnectInstance($registerVariableID, $SocketID);
					IPS_SetProperty($registerVariableID, "RXObjectID", $rxScriptID);
					IPS_SetHidden($registerVariableID, true); //Objekt verstecken
					IPS_ApplyChanges($registerVariableID);
				}				
			}
		}
		if(!isset($registerVariableID)) {
			$scriptsCatID = @IPS_GetObjectIDByIdent("TSKodi_scripts", $this->InstanceID);
			$newRegisterVariableID = IPS_CreateInstance("{F3855B3C-7CD6-47CA-97AB-E66D346C037F}");	
			IPS_SetName($newRegisterVariableID,"TSKodi RegisterVariable");
			IPS_ConnectInstance($newRegisterVariableID, $SocketID );
			IPS_SetProperty($newRegisterVariableID, "RXObjectID", $rxScriptID);
			IPS_SetHidden($newRegisterVariableID, true); //Objekt verstecken
			IPS_ApplyChanges($newRegisterVariableID);
			IPS_SetParent($newRegisterVariableID, $scriptsCatID); //verschieben
		}
*/






    

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

}
?>