<?
class TS_Fritzbox extends IPSModule
{
    
		public function Create()
		{
			//Never delete this line!
			parent::Create();
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyString("IPAddress", "0.0.0.0");
        $this->RegisterPropertyInteger("Port", 1012);
        $this->RegisterPropertyBoolean("Open", false);
        $this->RegisterPropertyString("an_tel1", "9979556" );
        $this->RegisterPropertyInteger("runskript1", 0 );
        $this->RegisterPropertyString("an_tel2", "12345" );
        $this->RegisterPropertyInteger("runskript2", 0 );
        
 //       $this->RequireParent("{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}", "Client Socket Fritz");

    }

//*********************************************************************************************************
    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        

        
        // Start create profiles
         
        // Start add scripts 
        $Script1 = '<?
/*
Bei Anruf Mord (Anruferkennung mit der FRITZ!Box) von Michael Steiner
Callmonitor ist ein Dienst auf dem Port 1012 einer FRITZ!Box Fon, 
an dem ein Client zur Anzeige eingehender Anrufe betrieben werden kann. 
Der Port 1012 muss einmalig per analogen Telefon aktiviert werden: 
Telefoncode zum Öffnen des TCP-Ports: #96*5* und zum Schließen des TCP-Ports: #96*4* 
Diese Funktion wir ab der Firmware Version xx.03.99 von AVM unterstützt. 
Die eingehenden Anrufe haben das Format:
Datum; RING; ConnectionID; Anrufer-Nr; Angerufene-Nummer;
In IP-Symcon muss ein "Client Socket" mit der IP-Adresse der Box und dem Port:1012 angelegt werden.
Zusätzlich noch eine "Register Variable" mit der Übergeordneten Instanz vom o.g. "Client Socket" 
sowie dem folgendem Skript:
*/
//27.09.15 20:01:46;RING;0;01703110095;8889640;SIP7;<CR><LF> //Socket
//27.09.15 20:03:11;RING;0;01703110095;8889640;SIP7;<CR><LF> //Reg Var
// 27.09.2015 20:06:49 | Register Variable | Von:01703110095 An:8889640
$fritzId = IPS_GetParent($_IPS["SELF"]);;
$an_tel1 = IPS_GetProperty($fritzId, "an_tel1");
$an_tel2 = IPS_GetProperty($fritzId, "an_tel2");
$runskript1 = IPS_GetProperty($fritzId, "runskript1");
$runskript2 = IPS_GetProperty($fritzId, "runskript2");


$callmonitor = explode(";", $_IPS["VALUE"]);
//echo ("Von:".$callmonitor[3]." An:".$callmonitor[4]);

if ($callmonitor[4] == $an_tel1){
  IPS_RunScript($runskript1);
}
if ($callmonitor[4] == $an_tel2){
  IPS_RunScript($runskript2);
}
?>';
  $Script1ID = $this->RegisterScript("_callmonitor", "_callmonitor",$Script1, 98);
  IPS_SetHidden($Script1ID,true);
  $sk_id=IPS_GetObjectIDByIdent('_callmonitor', $this->InstanceID);
  if ( IPS_ScriptExists($sk_id)){
      IPS_SetScriptContent ( $sk_id, $Script1);
  }


        // End add scripts

       // Start Register variables and Actions
        $InsID = @IPS_GetInstanceIDByName("Client Socket Fritz", 0);
        if ($InsID === false){
            $InsID = IPS_CreateInstance("{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}");
            IPS_SetName($InsID, "Client Socket Fritz"); // Instanz benennen

        }
        IPS_SetProperty($InsID, "Host", ($this->ReadPropertyString("IPAddress")) ); 
        IPS_SetProperty($InsID, "Port", ($this->ReadPropertyInteger("Port")) ); 
        IPS_SetProperty($InsID, "Open", ($this->ReadPropertyBoolean("Open")) ); 
        IPS_ApplyChanges($InsID); //Neue Konfiguration übernehmen

        $regID = @IPS_GetInstanceIDByName("RegisterVar_Fritz", $InsID);
        if ($regID === false){
        $regID = IPS_CreateInstance("{F3855B3C-7CD6-47CA-97AB-E66D346C037F}");
        IPS_SetName($regID, "RegisterVar_Fritz"); // Instanz benennen
        IPS_SetParent($regID, $InsID); // Instanz einsortieren unter dem Objekt mit der ID "12345"
        IPS_ConnectInstance($regID, $InsID);
        }
        IPS_SetProperty($regID, "RXObjectID", $Script1ID); 
        IPS_DisconnectInstance($regID);
        IPS_ConnectInstance($regID, $InsID);
        IPS_ApplyChanges($regID); //Neue Konfiguration übernehmen

//  		 $this->RegisterVariableString("zeile", "zeile", "",10);
 //       $this->RegisterVariableBoolean("Status", "Status", "",100);

    }


//*********************************************************************************************************
    protected function RequireParent($ModuleID, $Name = '')
    {

        $instance = IPS_GetInstance($this->InstanceID);
        if ($instance['ConnectionID'] == 0)
        {

            $parentID = IPS_CreateInstance($ModuleID);
            $instance = IPS_GetInstance($parentID);
            if ($Name == '')
                IPS_SetName($parentID, $instance['ModuleInfo']['ModuleName']);
            else
                IPS_SetName($parentID, $Name);
            IPS_ConnectInstance($this->InstanceID, $parentID);
        }
    }

}
?>
