<?
class TS_Muellabfuhr extends IPSModule
{
    
		public function Create()
		{
			//Never delete this line!
			parent::Create();
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyString("Tag_", "mon");
        $this->RegisterPropertyInteger("StartWoche",1); 
        $this->RegisterPropertyString("Start_tonne1", "Gruen");
        $this->RegisterPropertyString("Start_tonne2", "Grau + Gelb");
        $this->RegisterPropertyString("Start_tonne3", "Gruen");
        $this->RegisterPropertyString("Start_tonne4", "Blau + Gelb");
            
    }

//*********************************************************************************************************
    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        
        // Start create profiles
         
        // Start add scripts 
        // Start add scripts 
        $skript = '<?
$meldung_ID  = IPS_GetObjectIDByName("Meldung", IPS_GetParent($_IPS["SELF"]));
$meldungtext_ID  = IPS_GetObjectIDByName("Text", IPS_GetParent($_IPS["SELF"]));
$feiertag_ID  = IPS_GetObjectIDByName("Feiertag", IPS_GetParent($_IPS["SELF"]));
$Tag_ = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Tag_");
$StartWoche = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "StartWoche");
$Start_tonne1 = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Start_tonne1");
$Start_tonne2 = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Start_tonne2");
$Start_tonne3 = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Start_tonne3");
$Start_tonne4 = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Start_tonne4");
SetValueBoolean($feiertag_ID, false);

switch ($Tag_) {
    case "mon":
        $Abholtag=1;
        break;
    case "tue":
        $Abholtag=2;
        break;
    case "wed":
        $Abholtag=3;
        break;
    case "thu":
        $Abholtag=4;
        break;
    case "fri":
        $Abholtag=5;
        break;
    case "sat":
        $Abholtag=6;
        break;
}

$heuteD = date("d");
$heuteM = date("m");
$heuteY = date("Y");

$test = mktime( 0, 0, 0, $heuteM, $heuteD, $heuteY );
$wochentag_warn = date("w", $test); // w - Wochentag (0(Sonntag) bis 6(Samstag))
$Tag_warn = $Abholtag - 1;

$j=0;
for($i=$StartWoche;$i<=53;$i++) {
//$StartWoche=$StartWoche + 2;
$j=$j+1;
if($j == 1) { $Start_tonne = $Start_tonne1;}
if($j == 2) { $Start_tonne = $Start_tonne2;}
if($j == 3) { $Start_tonne = $Start_tonne3;}
if($j == 4) {
  $Start_tonne = $Start_tonne4;
  $j = 0;
  }
$_woche[$i] = $Start_tonne;
}

$suchtag = strtotime("next ".$Tag_);// Nächster Abholtag (Montag - Samstag)
$datum = date("d.m.Y", $suchtag);
$woche = intval(date("W", $suchtag));     // W - Wochennummer des Jahres (z.B.: 28)
$wochentag = date("w", $suchtag); // w - Wochentag (0(Sonntag) bis 6(Samstag))

if($wochentag_warn == $Tag_warn) {
        SetValueBoolean($meldung_ID, true);
  } else {
        SetValueBoolean($meldung_ID, false);
}
$abfuhrtermine = array ( "u" => array ( $Abholtag => $_woche[$woche]),
                         "g" => array ( $Abholtag => $_woche[$woche]));

$tag[0] = "So";
$tag[1] = "Mo";
$tag[2] = "Di";
$tag[3] = "Mi";
$tag[4] = "Do";
$tag[5] = "Fr";
$tag[6] = "Sa";

$feiertag_diese_woche = GetValueBoolean($feiertag_ID);

$heuteD = date("d");
$heuteM = date("m");
$heuteY = date("Y");

$sondertag ="";
$feiertag = array_search($suchtag, getGermanPublicHolidays(date("Y",$suchtag)));
If($feiertag == "Montag_vor_Ostern"){
$sondertag= "Montag_vor_Ostern";
//echo $sondertag;
$feiertag = "";
}
If($feiertag == "sondertag2"){
$sondertag= "sondertag2";
//echo $sondertag;
$feiertag = "";
}

if ( $feiertag <> "" AND !($wochentag == 0) ) {
    $array = ""; for($i = 1; $i <= 4; $i++) $array["id".$i] = 0;  
    SetValueBoolean($feiertag_ID, true);
    return;
}

// Feiertagsmerker am Sonntag zurücksetzen
If ($wochentag == 0 )
    SetValueBoolean($feiertag_ID, false);

// Prüfung auf Gerade oder Ungerade Woche
if($woche % 2 == 0) {
    $gu = "g";
} else {
    $gu = "u";
}

if  ( !$feiertag_diese_woche ) {
    if ( isset($abfuhrtermine[$gu][$wochentag])) {
 	   if ($sondertag == "Montag_vor_Ostern"){
          echo $sondertag;
			 $text=$abfuhrtermine[$gu][$wochentag];
          $sat = mktime( 0, 0, 0, $heuteM, $heuteD-1, $heuteY );
          $datum = date("d.m.Y", $sat);
			 $woche = date("W", $sat);     // W - Wochennummer des Jahres (z.B.: 28)
			 $wochentag = date("w", $sat); // w - Wochentag (0(Sonntag) bis 6(Samstag))
          echo $datum;
          $ausgabe = $text.", SonderAbholung am ".$tag[$wochentag]." ".$datum;
		} else {
        $ausgabe = $abfuhrtermine[$gu][$wochentag].", Abholung am ".$tag[$wochentag]." ".$datum;
      }
    }
} elseif ( isset($abfuhrtermine[$gu][$wochentag-1])) {
    $ausgabe = $abfuhrtermine[$gu][$wochentag-1].", Abholung am ".$tag[$wochentag]." ".$datum . " (Verschiebung)";
}

SetValueString($meldungtext_ID, $ausgabe);

function getGermanPublicHolidays($year = null) {
    if(!$easter = easter_date($year)) return false;
    else {
        $holidays["Neujahr"]             = mktime(0,0,0,1,1,$year);
        $holidays["Rosenmontag"]         = strtotime("-48 days", $easter);
        $holidays["Tag der Arbeit"]      = mktime(0,0,0,5,1,$year);
        $holidays["Karfreitag"]          = strtotime("-2 days", $easter);
        $holidays["Ostern"]              = $easter;
        $holidays["Ostersonntag"]        = $easter;
        $holidays["Ostermontag"]         = strtotime("+1 day", $easter);
        $holidays["Himmelfahrt"]         = strtotime("+39 days", $easter);
        $holidays["Pfingsten"]           = strtotime("+49 days", $easter);
        $holidays["Pfingstsonntag"]      = strtotime("+49 days", $easter);
        $holidays["Pfingstmontag"]       = strtotime("+50 days", $easter);
        $holidays["Fronleichnam"]        = strtotime("+60 days", $easter);
        $holidays["Tag der Einheit"]     = mktime(0,0,0,10,3,$year);
        $holidays["Allerheiligen"]     = mktime(0,0,0,10,3,$year);
        $holidays["Heiligabend"]         = mktime(0,0,0,11,1,$year);
        $holidays["1. Weihnachtsfeiertag"] = mktime(0,0,0,12,25,$year);
        $holidays["2. Weihnachtsfeiertag"] = mktime(0,0,0,12,26,$year);
        $holidays["Silvester"]           = mktime(0,0,0,12,31,$year);
        $holidays["1. Advent"]           = strtotime("1 sunday", mktime(0,0,0,11,26,$year));
        $holidays["2. Advent"]           = strtotime("2 sunday", mktime(0,0,0,11,26,$year));
        $holidays["3. Advent"]           = strtotime("3 sunday", mktime(0,0,0,11,26,$year));
        $holidays["4. Advent"]           = strtotime("4 sunday", mktime(0,0,0,11,26,$year));
		  //Sonderdinge müssen noch abgefangen werden
		  // Montag vor Ostern
		  $holidays["Montag_vor_Ostern"]       = strtotime("-6 days", $easter);
		  // Montag nach 4 Advent,
		  // immer SA vorher
        //$holidays["Montag_vor_Ostern"]       = strtotime("-6 days", $easter);
        return $holidays;
    }
}
?>';
  $skriptD = $this->RegisterScript("_abfall", "_abfall", $skript, 99); 

        IPS_SetHidden($skriptD,true);
  $sk_id=IPS_GetObjectIDByIdent('_abfall', $this->InstanceID);
  if ( IPS_ScriptExists($sk_id)){
      IPS_SetScriptContent ( $sk_id, $skript);
  }         
        // End add scripts

       // Start Register variables and Actions
   		 $this->RegisterVariableString("Text", "Text", "",10);
       $this->RegisterVariableBoolean("Feiertag", "Feiertag", "",100);
       $this->RegisterVariableBoolean("Meldung", "Meldung", "",100);
 
       $_timer = $this->Registerevent_timer($skriptD); 

    }


//*********************************************************************************************************
    private function Registerevent_timer($TargetID)
		{ 
      if(!isset($_IPS))
      global $_IPS;  
      $EreignisID = @IPS_GetEventIDByName("E_timer", $TargetID);
      if ($EreignisID == true){
      if (IPS_EventExists(IPS_GetEventIDByName ( "E_timer", $TargetID)))
      {
       IPS_DeleteEvent(IPS_GetEventIDByName ( "E_timer", $TargetID));
      }
      }       
      $eid = IPS_CreateEvent(1);                  //Ausgelöstes Ereignis
      IPS_SetName($eid, "E_timer");
      IPS_SetEventCyclicTimeFrom($eid, 16, 0, 11);
      IPS_SetParent($eid, $TargetID);         //Ereignis zuordnen
      IPS_SetEventActive($eid, true);             //Ereignis aktivieren
    }	

}
?>
