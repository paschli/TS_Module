<?
class TS_xbmc extends IPSModule
{
    
		public function Create()
		{
			//Never delete this line!
			parent::Create();
        
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.

        $this->RegisterPropertyString("IPAddress", "192.168.1.55");
        $this->RegisterPropertyString("Port","8080");
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

//*********************************************************************************************************
    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        
        // Start create profiles
        $this->RegisterProfileIntegerEx("kodi.FB", "Information", "", "", Array(
                        //Array(0, "frei",  "", -1),
                        Array(1, "Power", "", -1),
                        Array(2, "red", "", 16711680),
                        Array(3, "green", "", 65280 ),
                        Array(4, "yellow", "", 16776960),
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
                        Array(25, "Info", "", -1),
                        Array(26, "Back", "", -1),
 
        ));
//        $s1= $this->ReadPropertyString("S1_Name");

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
/*
        $this->RegisterProfileIntegerEx("kodi.Sender", "Information", "", "", Array(
                        //Array(0, "frei",  "", -1),
                        Array(1, "Sport1", "", -1),
                        Array(2, "RTL", "", -1),
                        Array(3, "ARD", "", -1),
                        Array(4, "ZDF", "", -1),
                        Array(5, "WDR", "", -1),
                        Array(6, "ORF1", "", -1),
                        Array(7, "ORF2", "", -1),
                        Array(8, "Disc", "", -1),
                        Array(9, "Film", "", -1),
                        Array(10, "Aktion", "", -1),

        ));
*/
         
        // Start add scripts 

        // End add scripts

       // Start Register variables and Actions
  			$Status_id  = $this->RegisterVariableInteger("FB", "FB", "kodi.FB",10);
        $this->EnableAction("FB");

  			$Station_id = $this->RegisterVariableInteger("Station", "Station", "kodi.Sender",20);
        $this->EnableAction("Station");

    }

    public function FB($_steuer)
    {
      $ip= $this->ReadPropertyString("IPAddress");
      $port= $this->ReadPropertyString("Port");
      $_control="/control/rcem?KEY_";
      if (Sys_Ping($this->ReadPropertyString("IPAddress"), 100) == true) {		
        if ($_steuer === 1) ($_befehl="POWER");
        if ($_steuer === 2) ($_befehl="RED" );
        if ($_steuer === 3) ($_befehl="GREEN" );
        if ($_steuer === 4) ($_befehl="YELLOW" );
        if ($_steuer === 5) ($_befehl="Input.Select%22" );
        if ($_steuer === 25)($_befehl="Input.Info%22" );
        if ($_steuer === 26)($_befehl="Input.Back%22" );
        if ($_steuer === 6) ($_befehl="Input.Up%22" );
        if ($_steuer === 7) ($_befehl="Input.Left%22" );
        if ($_steuer === 8) ($_befehl="Input.Right%22" );
        if ($_steuer === 9) ($_befehl="Input.Down%22" );
        if ($_steuer === 10)($_befehl="Input.Ok%22" );
        if ($_steuer === 11)($_befehl="Input.Home%22" );
        if ($_steuer === 12)($_befehl="Application.SetVolume%22,%22params%22:{%22volume%22:%22decrement%22}" );
        if ($_steuer === 13)($_befehl="Application.SetVolume%22,%22params%22:{%22volume%22:%22increment%22}" );
        if ($_steuer === 14)($_befehl="Application.SetMute%22,%22params%22:{%22mute%22:%22toggle%22}" );
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
        $_t3= "http://".$ip.":".$port."/jsonrpc?request={%22jsonrpc%22:%222.0%22,%22id%22:1,%22method%22:%22".$_befehl."}";
        $Ausgabe = fopen("$_t3", "r");
//      return ($Ausgabe);
       }  
    }


    public function Station($_steuer)
    {
      $ip = $this->ReadPropertyString("IPAddress");
      $port= $this->ReadPropertyString("Port");      
      $_t3="";
      if (Sys_Ping($ip, 100) == true) {		
// Abfrage 
// http://192.168.1.66/jsonrpc?request={%22jsonrpc%22:%222.0%22,%22method%22:%22PVR.GetChannels%22,%22params%22:{%22channelgroupid%22:1},%20%22id%22:1}

//http://192.168.1.66/jsonrpc?request={%20%22jsonrpc%22:%20%222.0%22,%20%22method%22:%20%22Player.Open%22,%20%22params%22:%20{%20%22item%22:%20{%20%22channelid%22:%20273}%20},%20%22id%22:1}
// Wohnzimmer
/*
        if ($_steuer === 1)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:32}}");   //Sport1
        if ($_steuer === 2)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:285}}"); //RTL
        if ($_steuer === 3)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:271}}"); //ARD
        if ($_steuer === 4)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:303}}"); //ZDF
        if ($_steuer === 5)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:58}}");  //WDR Bi
        if ($_steuer === 6)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:446}}"); //ORF1
        if ($_steuer === 7)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:449}}"); //ORF2
        if ($_steuer === 8)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:180}}"); //disc
        if ($_steuer === 9)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:95}}"); //Film
        if ($_steuer === 10)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:398}}"); //Action
*/
//Büro
/*
        if ($_steuer === 1)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:1518}}");   //Sport1
        if ($_steuer === 2)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:1773}}"); //RTL
        if ($_steuer === 3)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:1759}}"); //ARD
        if ($_steuer === 4)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:1791}}"); //ZDF
        if ($_steuer === 5)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:1544}}");  //WDR Bi
        if ($_steuer === 6)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:1876}}"); //ORF1
        if ($_steuer === 7)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:1698}}"); //ORF2
        if ($_steuer === 8)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:1668}}"); //disc
        if ($_steuer === 9)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:1583}}"); //Film
        if ($_steuer === 10)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:1813}}"); //Action
*/
//Büro neu

        if ($_steuer === 1)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:".($this->ReadPropertyString("S1_Wert"))."}}");   //Sport1
        if ($_steuer === 2)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:".($this->ReadPropertyString("S2_Wert"))."}}"); //RTL
        if ($_steuer === 3)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:".($this->ReadPropertyString("S3_Wert"))."}}"); //ARD
        if ($_steuer === 4)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:".($this->ReadPropertyString("S4_Wert"))."}}"); //ZDF
        if ($_steuer === 5)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:".($this->ReadPropertyString("S5_Wert"))."}}");  //WDR Bi
        if ($_steuer === 6)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:".($this->ReadPropertyString("S6_Wert"))."}}"); //ORF1
        if ($_steuer === 7)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:".($this->ReadPropertyString("S7_Wert"))."}}"); //ORF2
        if ($_steuer === 8)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:".($this->ReadPropertyString("S8_Wert"))."}}"); //disc
        if ($_steuer === 9)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:".($this->ReadPropertyString("S9_Wert"))."}}"); //Film
        if ($_steuer === 10)($_befehl="Player.Open%22,%22params%22:{%22item%22:{%22channelid%22:".($this->ReadPropertyString("S10_Wert"))."}}"); //Action

        $_t3= "http://".$ip.":".$port."/jsonrpc?request={%22jsonrpc%22:%222.0%22,%22id%22:1,%22method%22:%22".$_befehl."}";
        $Ausgabe = fopen("$_t3", "r"); 
        //        return ($Ausgabe);
      }  
    }

################## ActionHandler

    public function RequestAction($Ident, $Value)
    {
 //echo ($Ident);

        switch ($Ident)
        {
            case "FB":
                $result = $this->FB($Value);
                break;
            case "Station":
                $result = $this->Station($Value);
                break;
            default:
                throw new Exception("Invalid ident");
        }
// echo ($result);
        if ($result == false)
        {
///            throw new Exception("Error on RequestAction for ident " . $Ident);
        }
    }

################## PRIVATE

//*********************************************************************************************************
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
