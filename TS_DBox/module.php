<?
class TS_Dbox extends IPSModule
{
    
		public function Create()
		{
			//Never delete this line!
			parent::Create();
        
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyString("IPAddress", "192.168.1.16");
      
    }

//*********************************************************************************************************
    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        
        // Start create profiles
        $this->RegisterProfileIntegerEx("Dbox.FB", "Information", "", "", Array(
                        //Array(0, "frei",  "", -1),
                        Array(1, "Power", "", -1),
                        Array(2, "red", "", 16711680),
                        Array(3, "green", "", 65280 ),
                        Array(4, "yellow", "", 16776960),
                        Array(5, "blue", "", 255),
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
                        Array(25, "Help", "", -1),
                        Array(26, "Setup", "", -1),
 
        ));
 
        $this->RegisterProfileIntegerEx("Dbox.Sender", "Information", "", "", Array(
                        //Array(0, "frei",  "", -1),
                        Array(1, "Sport1", "", -1),
                        Array(2, "RTL", "", -1),
                        Array(3, "ARD", "", -1),
                        Array(4, "ZDF", "", -1),
                        Array(5, "WDR", "", -1),
                        Array(6, "ORF1", "", -1),
                        Array(7, "ORF2", "", -1),

        ));

         
        // Start add scripts 

        // End add scripts

       // Start Register variables and Actions
  			$Status_id  = $this->RegisterVariableInteger("FB", "FB", "Dbox.FB",10);
        $this->EnableAction("FB");

  			$Station_id = $this->RegisterVariableInteger("Station", "Station", "Dbox.Sender",20);
        $this->EnableAction("Station");

    }

    public function FB($_dbox)
    {
      $ip= $this->ReadPropertyString("IPAddress");
      $_control="/control/rcem?KEY_";
      if (Sys_Ping($this->ReadPropertyString("IPAddress"), 100) == true) {		
        if ($_dbox === 1) ($_befehl="POWER");
        if ($_dbox === 2) ($_befehl="RED" );
        if ($_dbox === 3) ($_befehl="GREEN" );
        if ($_dbox === 4) ($_befehl="YELLOW" );
        if ($_dbox === 5) ($_befehl="BLUE" );
        if ($_dbox === 25)($_befehl="HELP" );
        if ($_dbox === 26)($_befehl="SETUP" );
        if ($_dbox === 6) ($_befehl="UP" );
        if ($_dbox === 7) ($_befehl="LEFT" );
        if ($_dbox === 8) ($_befehl="RIGHT" );
        if ($_dbox === 9) ($_befehl="DOWN" );
        if ($_dbox === 10)($_befehl="OK" );
        if ($_dbox === 11)($_befehl="HOME" );
        if ($_dbox === 12)($_befehl="VOLUMEDOWN" );
        if ($_dbox === 13)($_befehl="VOLUMEUP" );
        if ($_dbox === 14)($_befehl="MUTE" );
        if ($_dbox === 15)($_befehl="1" );
        if ($_dbox === 16)($_befehl="2" );
        if ($_dbox === 17)($_befehl="3" );
        if ($_dbox === 18)($_befehl="4" );
        if ($_dbox === 19)($_befehl="5" );
        if ($_dbox === 20)($_befehl="6" );
        if ($_dbox === 21)($_befehl="7" );
        if ($_dbox === 22)($_befehl="8" );
        if ($_dbox === 23)($_befehl="9" );
        if ($_dbox === 24)($_befehl="0" );
        $_t3= "http://".$ip.$_control.$_befehl;
        $Ausgabe = fopen("$_t3", "r");
//      return ($Ausgabe);
       }  
    }


    public function Station($_dbox)
    {
      $ip = $this->ReadPropertyString("IPAddress");
      $_t3="";
      if (Sys_Ping($ip, 100) == true) {		
        if ($_dbox === 1)($_t3= "http://".$ip."/control/exec?gljzap&3&4008500dd&16&x#akt");  //Sport1
        if ($_dbox === 2)($_t3= "http://".$ip."/control/exec?gljzap&5&44100012ee3&3&x#akt"); //RTL
        if ($_dbox === 3)($_t3= "http://".$ip."/control/exec?gljzap&1&44d00016dca&1&x#akt"); //ARD
        if ($_dbox === 4)($_t3= "http://".$ip."/control/exec?gljzap&5&43700016d66&1&x#akt"); //ZDF
        if ($_dbox === 5)($_t3= "http://".$ip."/control/exec?gljzap&3&4b100016e92&2&x#akt"); //WDR Bi
        if ($_dbox === 6)($_t3= "http://".$ip."/control/exec?gljzap&1&45d000132c9&8&x#akt"); //ORF1
        if ($_dbox === 7)($_t3= "http://".$ip."/control/exec?gljzap&2&45d000132ca&8&x#akt"); //ORF2
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
