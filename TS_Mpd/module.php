<?
class TS_MPD extends IPSModule 
{
    
		public function Create()
		{
			//Never delete this line!
			parent::Create();
        
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyString("Host", "192.168.1.103");
        $this->RegisterPropertyString("Port", "6600");
        $this->RegisterPropertyInteger("DefaultVolume", 15);
        $this->RegisterPropertyBoolean("MuteControl", true);
        $this->RegisterPropertyBoolean("Logo", true);
        $this->RegisterPropertyInteger("Rel_id", 22318);

        $this->RegisterPropertyString("FavoriteStation", "");
        $this->RegisterPropertyString("WebFrontStations", "<all>");
       
    }
    
    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        
        // Start create profiles
        $this->RegisterProfileIntegerEx("MPD.Status", "Information", "", "", Array(
                                             Array(0, "Prev",  "", -1),
                                             Array(1, "Play",  "", -1),
                                             Array(2, "Pause", "", -1),
                                             Array(3, "Stop",  "", -1),
                                             Array(4, "Next",  "", -1)
        ));
        $this->RegisterProfileInteger("MPD.Volume", "Intensity", "", " %",   0, 100, 1);
        $this->RegisterProfileIntegerEx("MPD.Switch", "Information", "", "", Array(
                                             Array(0, "Off", "", 0xFF0000),
                                             Array(1, "On",  "", 0x00FF00)
        ));
        
        //Build Radio Station Associations according to user settings
        include_once(__DIR__ . "/radio_stations.php");
        $Associations          = Array();
        $AvailableStations     = get_available_stations();
        $WebFrontStations      = $this->ReadPropertyString("WebFrontStations");
        $WebFrontStationsArray = array_map("trim", explode(",", $WebFrontStations));
        $FavoriteStation       = $this->ReadPropertyString("FavoriteStation");
        $Value                 = 1;
        
        foreach ( $AvailableStations as $key => $val ) {
            if (in_array( $val['name'], $WebFrontStationsArray) || $WebFrontStations === "<alle>" || $WebFrontStations === "<all>" ) {
                if  ( $val['name'] === $FavoriteStation ){
                    $Color = 0xFCEC00;
                } else {
                    $Color = -1;
                }
                $Associations[] = Array($Value++, $val['name'], "", $Color);
            }
        }
        
        if(IPS_VariableProfileExists("MPD.Radio"))
            IPS_DeleteVariableProfile("MPD.Radio");
        
        $this->RegisterProfileIntegerEx("MPD.Radio", "Speaker", "", "", $Associations);
        
   
        // Start Register variables and Actions
        // 1) general availabe
        $this->RegisterVariableString("nowPlaying", "nowPlaying", "", 20);
        $this->RegisterVariableInteger("Radio", "Radio", "MPD.Radio", 21);
        $this->RegisterVariableInteger("Status", "Status", "MPD.Status", 29);
        $this->RegisterVariableInteger("Volume", "Volume", "MPD.Volume", 30);

        $this->EnableAction("Radio");
        $this->EnableAction("Status");
        $this->EnableAction("Volume");
 
        $steuer_id = $this->ReadPropertyInteger("Rel_id");
        // 2) Add/Remove according to feature activation
        // create link list for deletion of liks if target is deleted
        $links = Array();
        foreach( IPS_GetLinkList() as $key=>$LinkID ){
            $links[] =  Array( ('LinkID') => $LinkID, ('TargetID') =>  IPS_GetLink($LinkID)['TargetID'] );
        }
        
        // 2c) Mute
/*
        $this->RegisterVariableInteger("Mute","Mute", "MPD.Switch", 31);
        if ($this->ReadPropertyBoolean("MuteControl")){
            $this->EnableAction("Mute");
            IPS_SetHidden($this->GetIDForIdent("Mute"),false);
        }else{
            $this->removeVariableAction("Mute", $links);
            IPS_SetHidden($this->GetIDForIdent("Mute"),true);
        }
*/
  	     $this->RegisterVariableString("Logo", "Logo", "~HTMLBox",200);
         if ( $this->ReadPropertyBoolean("Logo")){
              IPS_SetHidden($this->GetIDForIdent("Logo"),false);
         }else{
              IPS_SetHidden($this->GetIDForIdent("Logo"),true);
         }
         
         
        // End Register variables and Actions
        
        // Start add scripts for regular status and grouping updates


        // 1) _updateStatus 
        $statusScriptID = $this->RegisterScript("_updateStatus", "_updateStatus", '<?
    include_once("../modules/Ts_Module/TS_Mpd/status.php");

?>', 98);
        IPS_SetHidden($statusScriptID,true);
        IPS_SetScriptTimer($statusScriptID, 0); 
        $this->Registerevent1($statusScriptID,$steuer_id); 





        
}
    
    /**
    * This function will be available automatically after the module is imported with the module control.
    * Using the custom prefix this function will be callable from PHP and JSON-RPC through:
    *
    * TSMPD_Play($id);
    *
    */

    public function Play()
    {
      if (Sys_Ping($this->ReadPropertyString("Host"), 1000) == true) {		
        SetValue($this->GetIDForIdent("Status"), 1);
        include_once(__DIR__ . "/mpd.php");
       (new MPD($this->ReadPropertyString("Host"),$this->ReadPropertyString("Port"),''))->Play(0);
      }  
    }
    
    public function Pause()
    {
      if (Sys_Ping($this->ReadPropertyString("Host"), 1000) == true) {		
        SetValue($this->GetIDForIdent("Status"), 2);
        include_once(__DIR__ . "/mpd.php");
        (new MPD($this->ReadPropertyString("Host"),$this->ReadPropertyString("Port"),''))->pause(1);
      }  
    }
    
    public function Previous()
    {
      if (Sys_Ping($this->ReadPropertyString("Host"), 1000) == true) {		
        include_once(__DIR__ . "/mpd.php");
        (new MPD($this->ReadPropertyString("Host"),$this->ReadPropertyString("Port"),''))->Previous();
      }  
    }
    
    public function Next()
    {
      if (Sys_Ping($this->ReadPropertyString("Host"), 1000) == true) {		
        include_once(__DIR__ . "/mpd.php");
        (new MPD($this->ReadPropertyString("Host"),$this->ReadPropertyString("Port"),''))->Next();
      }  
    }
    

    public function SetMute($mute)
    {
      if (Sys_Ping($this->ReadPropertyString("Host"), 1000) == true) {		
        if (!$this->ReadPropertyBoolean("MuteControl")) die("This function is not enabled for this instance");

        SetValue($this->GetIDForIdent("Mute"), $mute);
        include_once(__DIR__ . "/mpd.php");
        (new MPD($this->ReadPropertyString("Host"),$this->ReadPropertyString("Port"),''))->SetMute($mute);
      }  
    }
    
    public function SetVolume($volume)
    {
      if (Sys_Ping($this->ReadPropertyString("Host"), 1000) == true) {		
        SetValue($this->GetIDForIdent("Volume"), $volume);
        include_once(__DIR__ . "/mpd.php");
        (new MPD($this->ReadPropertyString("Host"),$this->ReadPropertyString("Port"),''))->setvol($volume);
      }  
    }

    public function SetDefaultVolume()
    {
        $this->SetVolume($this->ReadPropertyInteger("DefaultVolume"));
    }
    
    public function SetRadio($radio)
    {
      if (Sys_Ping($this->ReadPropertyString("Host"), 1000) == true) {		
        include_once(__DIR__ . "/mpd.php");
        include_once(__DIR__ . "/radio_stations.php");

        (new MPD($this->ReadPropertyString("Host"),$this->ReadPropertyString("Port"),''))->playlist_clear();
        (new MPD($this->ReadPropertyString("Host"),$this->ReadPropertyString("Port"),''))->playlist_add(get_station_url($radio), $radio);
        (new MPD($this->ReadPropertyString("Host"),$this->ReadPropertyString("Port"),''))->Play(0);
      }  
    }
    
   
    public function SetRadioFavorite()
    {
        $this->SetRadio($this->ReadPropertyString("FavoriteStation"));
    }
    
    public function Stop()
    {
      if (Sys_Ping($this->ReadPropertyString("Host"), 1000) == true) {		
        SetValue($this->GetIDForIdent("Status"), 3);
        include_once(__DIR__ . "/mpd.php");
        (new MPD($this->ReadPropertyString("Host"),$this->ReadPropertyString("Port"),''))->Stop();
      }  
    }
    
    public function RequestAction($Ident, $Value)
    {
        switch($Ident) {
            case "Mute":
                $this->SetMute($Value);
                break;
            case "Radio":
                $this->SetRadio(IPS_GetVariableProfile("MPD.Radio")['Associations'][$Value-1]['Name']);
                SetValue($this->GetIDForIdent($Ident), $Value);
                break;
            case "Status":
                switch($Value) {
                    case 0: //Prev
                        $this->Previous();
                        break;
                    case 1: //Play
                        $this->Play();
                        break;
                    case 2: //Pause
                        $this->Pause();
                        break;
                    case 3: //Stop
                        $this->Stop();
                        break;
                    case 4: //Next
                        $this->Next();
                        break;
                }
                break;
            case "Volume":
                $this->SetVolume($Value);
                break;
            default:
                throw new Exception("Invalid ident");
        }
    }

		private function Registerevent1($TargetID,$Ziel_id)
		{ 
      if(!isset($_IPS))
      global $_IPS;  
      $EreignisID = @IPS_GetEventIDByName("E_rel",  $TargetID);
      if ($EreignisID == true){
      if (IPS_EventExists(IPS_GetEventIDByName ( "E_rel", $TargetID)))
      {
       IPS_DeleteEvent(IPS_GetEventIDByName ( "E_rel", $TargetID));
      }
      }       
      $eid = IPS_CreateEvent(0);                  //Ausgelöstes Ereignis
      IPS_SetName($eid, "E_rel");
      IPS_SetEventTrigger($eid, 1, $Ziel_id);        //Bei Änderung von Variable 
      IPS_SetParent($eid, $TargetID);         //Ereignis zuordnen
      IPS_SetEventActive($eid, true);             //Ereignis aktivieren
    }	
    
    protected function removeVariable($name, $links){
        $vid = @$this->GetIDForIdent($name);
        if ($vid){
            // delete links to Variable
            foreach( $links as $key=>$value ){
                if ( $value['TargetID'] === $vid )
                     IPS_DeleteLink($value['LinkID']);
            }
            $this->UnregisterVariable($name);
        }
    }

    protected function removeVariableAction($name, $links){
        $vid = @$this->GetIDForIdent($name);
        if ($vid){
            // delete links to Variable
            foreach( $links as $key=>$value ){
                if ( $value['TargetID'] === $vid )
                     IPS_DeleteLink($value['LinkID']);
            }
            $this->DisableAction($name);
            $this->UnregisterVariable($name);
        }
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
