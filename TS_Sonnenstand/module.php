<?

	class TS_Sonnenstand extends IPSModule
	{

		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			//These lines are parsed on Symcon Startup or Instance creation
			//You cannot use variables here. Just static values.
//http://www.tankentanken.de/suche
      $this->RegisterPropertyFloat("Lat", 0);
      $this->RegisterPropertyFloat("Lon", 0);
      

		}
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
  	
 			$this->RegisterVariableFloat("Himmelsrichtung", "Himmelsrichtung", "");
 			$this->RegisterVariableFloat("Sonnenhoehe", "Sonnenhoehe", "");

$sid = $this->RegisterScript("Sonnenstand", "Sonnenstand", '<?
 
 
   // Geographische Koordinaten des Objekts  !!! müssen angepasst werden !!!
    $latitude = 51.969085;    // Breitengrad
    $longitude = 9.014476;    // Längengrad
    $latitude = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Lat");
    $longitude = IPS_GetProperty(IPS_GetParent($_IPS["SELF"]), "Lon");


    //$timestamp = mktime($hour,$minute,$second,$month,$day,$year);
    $timestamp = time();

    // Zerlege Datum und Uhrzeit, Umrechnung in Weltzeit
   $monat     = intval(gmdate("n",$timestamp));
   $tag         = intval(gmdate("j",$timestamp));
   $jahr     = intval(gmdate("Y",$timestamp));
   $stunde     = intval(gmdate("G",$timestamp));
   $minute     = intval(gmdate("i",$timestamp));
   $sekunde = intval(gmdate("s",$timestamp));

    // Berechnungen
    // Ekliptikalkoordinaten der Sonne
   $jd12 = gregoriantojd($monat,$tag, $jahr);
   $stundenanteil = $stunde >= 12 ? (($stunde)/24+$minute/(60*24)+$sekunde/(60*60*24))-0.5 : (-1*($stunde/24-$minute/(24*60)-$sekunde/(24*60*60)));
   $jd12h = $jd12 + $stundenanteil;
   $n = $jd12h - 2451545;
   $L = 280.460 + 0.9856474 * $n;
   $g = 357.528 + 0.9856003 * $n;
   $i = intval($L / 360);
   $L = $L - $i*360;
   $i = intval($g / 360);
   $g = $g - $i*360;
   $e = 0.0167;
   $eL = $L + (2*$e * sin($g/180*M_PI)+ 5/4*$e*$e*sin(2*$g/180*M_PI))*180/pi();
   $epsilon = 23.439 - 0.0000004 * $n;
   $alpha = atan(cos($epsilon/180*M_PI)*sin($eL/180*M_PI)/cos($eL/180*M_PI))*180/M_PI;


   if ((cos($eL/180*M_PI)<0)) $alpha += 180;

   $delta = asin(sin($epsilon/180*M_PI)*sin($eL/180*M_PI))*180/M_PI;
   $jd0 = $jd12 - 0.5;
   $T0 = ($jd0 - 2451545.0) / 36525;
   $mittlere_sternzeit_greenwich = 6.697376 + 2400.05134 * $T0 + 1.002738 * ($stunde+$minute/60+$sekunde/3600);
   $i = intval($mittlere_sternzeit_greenwich / 24);
   $mittlere_sternzeit_greenwich = $mittlere_sternzeit_greenwich - $i*24;
   $stundenwinkel_fruehling_greenwich = $mittlere_sternzeit_greenwich * 15;
   $stundenwinkel_fruehling = $stundenwinkel_fruehling_greenwich + $longitude;
   $stundenwinkel_sonne = $stundenwinkel_fruehling - $alpha;
   $nenner = cos($stundenwinkel_sonne/180*M_PI)*sin($latitude/180*M_PI)-tan($delta/180*M_PI)*cos($latitude/180*M_PI);
   $azimut = atan(sin($stundenwinkel_sonne/180*M_PI)/$nenner)*180/M_PI;

   if ($nenner<0) $azimut+=180;
       if ($azimut>180) $azimut -= 360;
       $h = asin(cos($delta/180*M_PI)*cos($stundenwinkel_sonne/180*M_PI)*cos($latitude/180*M_PI)+sin($delta/180*M_PI)*sin($latitude/180*M_PI))*180/M_PI;
       $R = 1.02 / (tan(($h+10.3/($h+5.11))/180*M_PI));
       $elevation = round($h + ($R/60),1);

    // Von Norden ( 0 Grad) an berechnen
    $azimut = round ( $azimut += 180,1);

    // Himmelsrichtung der Sonne
//    $SunDirectionNames = array("N", "NNO", "NO", "ONO", "O", "OSO", "SO", "SSO", "S", "SSW", "SW", "WSW", "W", "WNW", "NW", "NNW");
//    $SunDirectionName = $SunDirectionNames[(int)((round($azimut)/ 22.5))];

//    echo "Himmelsrichtung " .(string) $azimut . " Grad" ." [ " .$SunDirectionName . " ]";
//    echo "    Sonnenhöhe " .(string) $elevation . " Grad" ;

    SetValue(IPS_GetVariableIDByName("Himmelsrichtung", IPS_GetParent($_IPS[\'SELF\']) ), $azimut);

    SetValue(IPS_GetVariableIDByName("Sonnenhoehe", IPS_GetParent($_IPS[\'SELF\']) ), $elevation);
?>');
    IPS_SetHidden($sid,true);
    IPS_SetScriptTimer($sid, 60); 
	}
  }
?>