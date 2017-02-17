<?
require_once(__DIR__ . "/../HomeKitService.php");

//class TS_HBAirQualitySensor extends IPSModule {
class TS_HBAirQualitySensor extends HomeKitService {

//class IPS_HomebridgeAirQualitySensor extends HomeKitService {
  public function Create() {
      //Never delete this line!
      parent::Create();
      $this->ConnectParent("{86C2DE8C-FB21-44B3-937A-9B09BB66FB76}");
      //Anzahl die in der Konfirgurationsform angezeigt wird - Hier Standard auf 1
      $this->RegisterPropertyInteger("Anzahl",1);
      //99 Geräte können pro Konfirgurationsform angelegt werden
      for($count = 1; $count -1 < 99; $count++) {
        $DeviceName = "DeviceName{$count}";
        $AirQualitySensorID = "AirQualitySensorID{$count}";
        $VOCDensity = "VOCDensity{$count}";
        $AirQuality = "AirQuality{$count}";
        $AirQualitySensorCurrent = "AirQualitySensorCurrent{$count}";

        $this->RegisterPropertyString($DeviceName, "");
        $this->RegisterPropertyInteger($AirQualitySensorID, 0);
        //VOCDensity
        $this->RegisterPropertyInteger($VOCDensity, 0);
        //AirQuality
        $this->RegisterPropertyInteger($AirQuality, 0);

        $this->RegisterPropertyInteger($AirQualitySensorCurrent, 0);

        $this->SetBuffer($DeviceName." VOCDensity ".$VOCDensity,"");
        $this->SetBuffer($DeviceName." AirQuality ".$AirQuality,"");
        $this->SetBuffer($DeviceName." AirQualitySensorCurrent ".$AirQualitySensorCurrent,"");
      }
  }
  public function ApplyChanges() {
      //Never delete this line!
      parent::ApplyChanges();
      //Setze Filter für ReceiveData
      $this->SetReceiveDataFilter(".*AirQualitySensor.*");
      $anzahl = $this->ReadPropertyInteger("Anzahl");

      for($count = 1; $count-1 < $anzahl; $count++) {
        $Devices[$count]["DeviceName"] = $this->ReadPropertyString("DeviceName{$count}");
        $Devices[$count]["VOCDensity"] = $this->ReadPropertyInteger("VOCDensity{$count}");
        $Devices[$count]["AirQuality"] = $this->ReadPropertyInteger("AirQuality{$count}");
        $Devices[$count]["AirQualitySensorCurrent"] = $this->ReadPropertyInteger("AirQualitySensorCurrent{$count}");

        //Buffernamen
        $BufferNameVOCDensity = $Devices[$count]["DeviceName"]." VOCDensity";
        $BufferNameAirQuality = $Devices[$count]["DeviceName"]." AirQuality";
//        $BufferNameAirQualitySensorCurrent = $Devices[$count]["DeviceName"]." AirQualitySensorCurrent";

        //Alte Registrierungen auf Variablen Veränderung aufheben
        $UnregisterBufferIDs = [];
        array_push($UnregisterBufferIDs,$this->GetBuffer($BufferNameVOCDensity));
        array_push($UnregisterBufferIDs,$this->GetBuffer($BufferNameAirQuality));
//        array_push($UnregisterBufferIDs,$this->GetBuffer($BufferNameAirQualitySensorCurrent));
        $this->UnregisterMessages($UnregisterBufferIDs, 10603);

        if ($Devices[$count]["DeviceName"] != "") {
          //Regestriere State Variable auf Veränderungen
          $RegisterBufferIDs = [];
          array_push($RegisterBufferIDs,$Devices[$count]["VOCDensity"]);
          array_push($RegisterBufferIDs,$Devices[$count]["AirQuality"]);
//          array_push($RegisterBufferIDs,$Devices[$count]["AirQualitySensorCurrent"]);
          $this->RegisterMessages($RegisterBufferIDs, 10603);

          //Buffer mit den aktuellen Variablen IDs befüllen
          $this->SetBuffer($BufferNameVOCDensity,$Devices[$count]["VOCDensity"]);
          $this->SetBuffer($BufferNameAirQuality,$Devices[$count]["AirQuality"]);
//          $this->SetBuffer($BufferNameAirQualitySensorCurrent,$Devices[$count]["AirQualitySensorCurrent"]);

          //Accessory anlegen
          $this->addAccessory($Devices[$count]["DeviceName"]);
        }
        else {
          return;
        }
      }
      $DevicesConfig = serialize($Devices);
      $this->SetBuffer("AirQualitySensor Config",$DevicesConfig);
    }

  public function Destroy() {
  }

  public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
    $Devices = unserialize($this->getBuffer("AirQualitySensor Config"));
    if ($Data[1] == true) {
      $anzahl = $this->ReadPropertyInteger("Anzahl");

      for($count = 1; $count-1 < $anzahl; $count++) {
        $Device = $Devices[$count];

        $DeviceName = $Device["DeviceName"];
        $data = $Data[0];
        //Prüfen ob die SenderID gleich der Temperatur Variable ist, dann den aktuellen Wert an die Bridge senden
        switch ($SenderID) {
          case $Device["VOCDensity"]:
            $result = $data;
            //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
            $result = $this->ConvertVariable($variable, $luftgüte);
            $this->SetValueToIPS($variable,$variableObject,$result);
            break;

          case $Device["AirQuality"]:
            $Characteristic = "AirQuality";
            $result = $data;
            $VariableAirQualityID = $Device["AirQuality"];
            break;
/*
          case $Device["AirQualitySensorCurrent"]:
            $Characteristic = "AirQualitySensorCurrent";
            $result = number_format($data, 2, '.', '');
            break;

            $wert = $result;
            if ( $wert >= 450 && $wert <= 600 ) {   $luftgüte= 1; }
            if ( $wert >= 601 && $wert <= 800 ) {   $luftgüte= 2; }
            if ( $wert >= 801 && $wert <= 1000 ) {   $luftgüte= 3; }
            if ( $wert >= 1001 && $wert <= 1500 ) {   $luftgüte= 4; }
            if ( $wert >= 1501 && $wert <= 2000 ) {   $luftgüte= 5; }
            if ( $wert >= 2101) {   $luftgüte= 5; }
            $Characteristic = "AirQuality";
             $VariableAirQualityID = $Device["AirQuality"];
            $variable = IPS_GetVariable($VariableAirQualityID);
            $variableObject = IPS_GetObject($VariableAirQualityID);


*/
        }
        $this->sendJSONToParent("setValue", $Characteristic, $DeviceName, $result);
      }
    }
  }

  public function GetConfigurationForm() {
    $anzahl = $this->ReadPropertyInteger("Anzahl");
    $form = '{"elements":
              [
                { "type": "NumberSpinner", "name": "Anzahl", "caption": "Anzahl" },';
    // Zählen wieviele Felder in der Form angelegt werden müssen
    for($count = 1; $count-1 < $anzahl; $count++) {
      $form .= '{ "type": "ValidationTextBox", "name": "DeviceName'.$count.'", "caption": "Gerätename für die Homebridge" },';
      $form .= '{ "type": "SelectInstance", "name": "AirQualitySensorID'.$count.'", "caption": "Gerät" },';
      $form .= '{ "type": "SelectVariable", "name": "VOCDensity'.$count.'", "caption": "VOCDensity" },';

      $form .= '{ "type": "SelectVariable", "name": "AirQuality'.$count.'", "caption": "AirQuality" },';

      $form .= '{ "type": "SelectVariable", "name": "AirQualitySensorCurrent'.$count.'", "caption": "AirQualitySensorCurrent" },';
      $form .= '{ "type": "Button", "label": "Löschen", "onClick": "echo TS_HBAirQualitySensor_removeAccessory('.$this->InstanceID.','.$count.');" },';
      if ($count == $anzahl) {
        $form .= '{ "type": "Label", "label": "------------------" }';
      } else {
        $form .= '{ "type": "Label", "label": "------------------" },';
      }
    }
    $form .= ']}';
    return $form;
  }

  public function getVar($DeviceName, $Characteristic) {
    $Devices = unserialize($this->getBuffer("AirQualitySensor Config"));
    $anzahl = $this->ReadPropertyInteger("Anzahl");
    for($count = 1; $count -1 < $anzahl; $count++) {
      $Device = $Devices[$count];
      $name = $Device["DeviceName"];
      //Prüfen ob der übergebene Name zu einem Namen aus der Konfirgurationsform passt wenn ja Wert an die Bridge senden
      if ($DeviceName == $name) {
        switch ($Characteristic) {
          case 'VOCDensity':
            $VariableVOCDensityID = $Device["VOCDensity"];
            $result = intval(GetValue($VariableVOCDensityID));
            break;
          case 'AirQuality':
            $VariableAirQualityID = $Device["AirQuality"];
            $result = intval(GetValue($VariableAirQualityID));
            break;
/*
          case 'AirQualitySensorCurrent':
            $VariableAirQualitySensorCurrentID = $Device["AirQualitySensorCurrent"];
            $result = GetValue($VariableAirQualitySensorCurrentID);
            $result = number_format($result, 2, '.', '');
            break;
*/
        $this->sendJSONToParent("callback", $Characteristic, $DeviceName, $result);
        return;
      }
    }
  }
}
  public function setVar($DeviceName, $value, $Characteristic) {
    $Devices = unserialize($this->getBuffer("AirQualitySensor Config"));
    for($count = 1; $count -1 < $this->ReadPropertyInteger("Anzahl"); $count++) {
      $Device = $Devices[$count];
      //Prüfen ob der übergebene Name zu einem Namen aus der Konfirgurationsform passt
      $name = $Device["DeviceName"];
      if ($DeviceName == $name) {
        switch ($Characteristic) {
          case 'VOCDensity':
            $VariableVOCDensityID = $Device["VOCDensity"];
            $variable = IPS_GetVariable($VariableVOCDensityID);
            $variableObject = IPS_GetObject($VariableVOCDensityID);
          //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
            $result = $this->ConvertVariable($variable, $result);
            //Geräte Variable setzen
            $this->SetValueToIPS($variable,$variableObject,$result);
            break;

          case 'AirQuality':
            $VariableAirQualityID = $Device["AirQuality"];
            $variable = IPS_GetVariable($VariableAirQualityID);
            $variableObject = IPS_GetObject($VariableAirQualityID);
            //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
            $result = $this->ConvertVariable($variable, $result);
            $this->SetValueToIPS($variable,$variableObject,$result);
            break;
/*
          case 'AirQualitySensorCurrent':
            $VariableAirQualitySensorCurrentID = $Device["AirQualitySensorCurrent"];
            $variable = IPS_GetVariable($VariableAirQualitySensorCurrentID);
            $variableObject = IPS_GetObject($VariableAirQualitySensorCurrentID);
            $result = $this->ConvertVariable($variable, $value);
            $this->SetValueToIPS($variable,$variableObject,$result);
            break;
*/
        }
      }
    }
  }

  private function addAccessory($DeviceName) {
    //Payload bauen
    $payload["name"] = $DeviceName;
    $payload["service"] = "AirQualitySensor";

    $array["topic"] ="add";
    $array["payload"] = $payload;
    $data = json_encode($array);
    $SendData = json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Buffer" => $data));
    @$this->SendDataToParent($SendData);
  }
}
?>