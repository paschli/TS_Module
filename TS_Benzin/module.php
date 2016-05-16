<?

	class TS_Benzinpreise extends IPSModule
	{

		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			//These lines are parsed on Symcon Startup or Instance creation
			//You cannot use variables here. Just static values.
//http://www.tankentanken.de/suche
      $this->RegisterPropertyString("daten", "32825%20-%20Blomberg%2C%20Lippe-Großenmarpe/51.9727206/9.0172005");
      $this->RegisterPropertyInteger("umkreis", 15);

		}
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
  	
 			$this->RegisterVariableString("Benzin", "Benzin", "~HTMLBox");

$benzin = '<?
$Parent           						= IPS_GetParent($_IPS[\'SELF\']);
$ParentModul								= IPS_GetObject($Parent);
$url_str = IPS_GetProperty(IPS_GetParent($_IPS[\'SELF\']), "daten");
$url_str = str_replace(" ", "%20", $url_str);

$umkreis = IPS_GetProperty(IPS_GetParent($_IPS[\'SELF\']), "umkreis");
// Variablen identifizieren
$Benzin_ID 	= IPS_GetObjectIDByName(\'Benzin\', IPS_GetParent($_IPS[\'SELF\']));

$time = date("M d Y H:i:s", time() );
$out = GetFuelPrice($url_str,$umkreis);
// print_r($out);

function GetFuelPrice($locationUrl,$radius)
 {
  //Url zusammenbauen
  $providerUrl = "http://www.tankentanken.de/suche";  $dieselUrl = $providerUrl."/diesel/".$radius."/".$locationUrl."?sort=dist";
  $superUrl = $providerUrl."/supere5/".$radius."/".$locationUrl."?sort=dist";
  $bioUrl = $providerUrl."/supere10/".$radius."/".$locationUrl."?sort=dist";
  //Inhalt auslesen und Länge bestimmen
  $contentSuper = file($superUrl);
  $contentBio = file($bioUrl);
  $contentDiesel = file($dieselUrl);
  $lines = count($contentSuper);
  //Arrays durchgehen und Triggern
  $trigger = " | ";
  $arrayCounter = 0;
  for($i = 0; $i < $lines; $i++)
  {
   if(strpos($contentSuper[$i],$trigger) and !strpos($contentSuper[$i],"tankentanken"))
   {
    //Tankstelle und Straße formatieren
    $string = trim(CharDecode($contentSuper[$i]));
    $explode = explode($trigger,$string);
    $array[$arrayCounter]["Name"] = $explode[0]." ";
    $street = $explode[1];
    $street = RemoveDigits($street);
    $street = str_replace(" Straße"," Str",$street);
    $street = str_replace(" Str"," Straße",$street);
    $street = str_replace(".","",$street);
    $array[$arrayCounter]["Street"] = $street;
    //Ort formatieren
    $string = trim(strtolower(CharDecode($contentSuper[$i + 4])));
    $string = RemoveDigits($string);
    $string = strtoupper(substr($string,1,1)).substr($string,2,strlen($string));
    $array[$arrayCounter]["City"] = $string;
    //Entfernung formatieren
    $string = trim($contentSuper[$i + 12]);
    $string = str_replace(",",".",$string);
    $string = str_replace(" km","",$string);
    $array[$arrayCounter]["Distance"] = floatval($string);
    //Preise formatieren und bestimmen, ob Tankstelle offen oder geschlossen ist
    $string = trim($contentSuper[$i - 17]);
    if(strpos($string,"--"))
    {
       $array[$arrayCounter]["State"] = "Geschlossen";
       $array[$arrayCounter]["PriceSuper"] = 0;
       $array[$arrayCounter]["PriceBio"] = 0;
       $array[$arrayCounter]["PriceDiesel"] = 0;
    }
    else
    {
       $array[$arrayCounter]["State"] = "Geöffnet";
     $array[$arrayCounter]["PriceSuper"] = round(floatval(CharDecode(trim($contentSuper[$i - 17]))),2);
     $array[$arrayCounter]["PriceBio"] = round(floatval(CharDecode(trim($contentBio[$i - 17]))),2);
     $array[$arrayCounter]["PriceDiesel"] = round(floatval(CharDecode(trim($contentDiesel[$i - 17]))),2);
     //print($contentBio[$i - 17]."\n");
    }
    //Counter erhöhen
    $arrayCounter++;
   }
  }
  return $array;
 }

function CharDecode($string)
 //$string = Eingabestring mit Sonderzeichen
 //Rückgabe = Ausgabestring mit Umlauten
 {

  $string = strip_tags($string);
  $string = html_entity_decode($string);

  $string = str_replace("         ", " ", $string);
  $string = str_replace("Ã¶", "ö", $string);
  $string = str_replace("Ãœ", "Ü", $string);
  $string = str_replace("Ã¼", "ü", $string);
  $string = str_replace("Ã¤", "ä", $string);
  $string = str_replace("ÃŸ", "ß", $string);
  $string = str_replace("&amp;ndash;", "-", $string);
  $string = str_replace(\'&amp;#039;\', "`", $string);
  $string = str_replace("&amp;", "&", $string);
  $string = str_replace("Ã–", "Ö", $string);
  $string = str_replace("Ã„", "Ä", $string);
  $string = str_replace("â€ž", "\"", $string);
  $string = str_replace("â€œ", "\"", $string);
  $string = str_replace("&ndash;", "-", $string);
  $string = str_replace("â€ž", "\"", $string);
  $string = str_replace("Â“", "\"", $string);
  $string = str_replace("Â„", "\"", $string);
  $string = str_replace("Â–", "-", $string);
  $string = str_replace("â€“", "-", $string);
  $string = str_replace("f r", "für", $string);
  $string = str_replace("Â?ber", "Über", $string);
  $string = str_replace("%C3%A4", "ä", $string);
  $string = str_replace("%C3%B6", "ö", $string);
  $string = str_replace("%C3%BC", "ü", $string);
  $string = str_replace("%C3%84", "Ä", $string);
  $string = str_replace("%C3%96", "Ö", $string);
  $string = str_replace("%C3%9C", "Ü", $string);
  $string = str_replace("%C3%9F", "ß", $string);
  $string = str_replace("=C3=A4", "ä", $string);
  $string = str_replace("=C3=B6", "ö", $string);
  $string = str_replace("=C3=BC", "ü", $string);
  $string = str_replace("=C3=84", "Ä", $string);
  $string = str_replace("=C3=96", "Ö", $string);
  $string = str_replace("=C3=9C", "Ü", $string);
  $string = str_replace("=C3=9F", "ß", $string);
  $string = str_replace("%20", " ", $string);
  $string = preg_replace("/\r|\n/s", " ", $string);

  return $string;
 }

 function RemoveDigits($string)
 //Entfernt aus dem String alle Ziffern
 {
    $string = str_replace("0","",$string);
    $string = str_replace("1","",$string);
    $string = str_replace("2","",$string);
    $string = str_replace("3","",$string);
    $string = str_replace("4","",$string);
    $string = str_replace("5","",$string);
    $string = str_replace("6","",$string);
    $string = str_replace("7","",$string);
    $string = str_replace("8","",$string);
    $string = str_replace("9","",$string);
    return $string;
 }






//Array erzeugen
$fuelArray = GetFuelPrice($url_str,$umkreis);
 //Array sortieren
/*
 function cmpMulti($a,$b)
 {
    if($a["PriceSuper"] == $b["PriceSuper"])
    {
       if($a["Distance"] == $b["Distance"])
   {
    return 0;
   }
   else
         {
    return ($a["Distance"] < $b["Distance"]) ? -1 : +1;
   }
  }
    else
    {
    return ($a["PriceSuper"] < $b["PriceSuper"]) ? -1 : +1;
  }
 }
 usort($fuelArray,"cmpMulti");
*/
 //HTML-String
 $string = "";
 $string .= "<br><p><font size=2 color=red><b>".$time." </b></font>";

 foreach($fuelArray as $row)
 {
  if($row["State"] == "Geöffnet")
  {
/*
   $string .= "<br><blockquote><p><font size=2 color=white><b>".$row["Name"]." Tankstelle</b></font></blockquote>";
   $string .= "<blockquote><font size=2 color=lightgrey>".$row["Street"].", </font><font size=2 color=white><b>".$row["City"]."</b></font><font size=2 color=lightgrey> - ".$row["Distance"]." km entfernt</font></blockquote><p>";
   $string .= "<blockquote><table border=0><tr><td width=300><font size=2><b>Super E5</b></font></td><td width=300><font size=2><b>Super E10</b></font></td><td width=300><font size=2><b>Diesel</b></font></td></tr></table>";
   if($row["PriceSuper"] > 10) $super = "<td width=300><font size=2 color=dimgrey><b>N/A</b></font></td>"; else $super = "<td width=300><font size=2 color=darkorange><b>".number_format($row["PriceSuper"],2)." EUR</b></font></td>";
   if($row["PriceBio"] > 10) $bio = "<td width=300><font size=2 color=dimgrey><b>N/A</b></font></td>"; else $bio = "<td width=300><font size=2 color=forestgreen><b>".number_format($row["PriceBio"],2)." EUR</b></font></td>";
   if($row["PriceDiesel"] > 10) $diesel = "<td width=300><font size=2 color=dimgrey><b>N/A</b></font></td>"; else $diesel = "<td width=300><font size=2 color=grey><b>".number_format($row["PriceDiesel"],2)." EUR</b></font></td>";
   $string .= "<table border=0><tr>".$super.$bio.$diesel."</tr></table></blockquote><br>";
*/

   $string .= "<br><blockquote><p><font size=2 color=red><b>".$row["Name"]." </b></font>";
   $string .= "<font size=2 color=lightgrey>".$row["Street"].", </font><font size=2 color=white><b>".$row["City"]."</b></font><font size=2 color=lightgrey> - ".$row["Distance"]." km</font></blockquote><p>";
   $string .= "<blockquote><table border=0><tr><td width=300><font size=2><b>Super E5</b></font></td><td width=300><font size=2><b>Super E10</b></font></td><td width=300><font size=2><b>Diesel</b></font></td></tr></table>";
   if($row["PriceSuper"] > 10) $super = "<td width=300><font size=2 color=dimgrey><b>N/A</b></font></td>"; else $super = "<td width=300><font size=2 color=darkorange><b>".number_format($row["PriceSuper"],2)." EUR</b></font></td>";
   if($row["PriceBio"] > 10) $bio = "<td width=300><font size=2 color=dimgrey><b>N/A</b></font></td>"; else $bio = "<td width=300><font size=2 color=forestgreen><b>".number_format($row["PriceBio"],2)." EUR</b></font></td>";
   if($row["PriceDiesel"] > 10) $diesel = "<td width=300><font size=2 color=dimgrey><b>N/A</b></font></td>"; else $diesel = "<td width=300><font size=2 color=grey><b>".number_format($row["PriceDiesel"],2)." EUR</b></font></td>";
   $string .= "<table border=0><tr>".$super.$bio.$diesel."</tr></table></blockquote><br>";
  }
 }

 SetValue($Benzin_ID ,utf8_decode($string)); //Hier die InstanceID der String-Variable angeben
?>';
  $sid = $this->RegisterScript("_benzin", "_benzin", $benzin, 99);

    IPS_SetHidden($sid,true);
    IPS_SetScriptTimer($sid, 600);
  $sk_id=IPS_GetObjectIDByIdent('_benzin', $this->InstanceID);
  if ( IPS_ScriptExists($sk_id)){
      IPS_SetScriptContent ( $sk_id, $benzin);
  }
     
	}
  }
?>