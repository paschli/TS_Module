<?
class TS_Dummy extends IPSModule
{
    
		public function Create()
		{
			//Never delete this line!
			parent::Create();
        
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.

        $this->RegisterPropertyBoolean("Schalter", "false");      

    }

//*********************************************************************************************************
    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

            $this->RegisterVariableBoolean("Schalter","Schalter", "Switch", 1);
            $this->EnableAction("Schalter");
        
        // Start create profiles
         
        // Start add scripts 

        // End add scripts

       // Start Register variables and Actions

    }


################## ActionHandler
    public function SetSwitch($switch)
    {
        if ($this->ReadPropertyBoolean("Schalter")) SetValue($this->GetIDForIdent("Schalter"), $switch);
    }

    public function RequestAction($Ident, $Value)
    {
 //echo ($Ident);

        switch ($Ident)
        {
            case "Schalter":
                $result = $this->SetSwitch($Value);
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
}
?>
