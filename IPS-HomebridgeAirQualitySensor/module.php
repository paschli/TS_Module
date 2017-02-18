<?
require_once(__DIR__ . "/../HomeKitService.php");
class IPS_HomebridgeAirQualitySensor extends HomeKitService {
  public function Create() {
      //Never delete this line!
      parent::Create();
      //Anzahl die in der Konfirgurationsform angezeigt wird - Hier Standard auf 1
      $this->RegisterPropertyInteger("Anzahl",1);
      $this->ConnectParent("{86C2DE8C-FB21-44B3-937A-9B09BB66FB76}");
      //99 Geräte können pro Konfirgurationsform angelegt werden
      for($count = 1; $count -1 < 99; $count++) {
        $DeviceName = "DeviceName{$count}";
        $AirQualitySensorID = "AirQualitySensorID{$count}";
        $VOCDensity = "VOCDensity{$count}";
        $AirQuality = "AirQuality{$count}";
        $AirQualityOptional = "AirQualityOptional{$count}";
        $this->RegisterPropertyString($DeviceName, "");
        $this->RegisterPropertyInteger($AirQualitySensorID, 0);
        $this->RegisterPropertyInteger($VOCDensity, 0);
        $this->RegisterPropertyBoolean($AirQualityOptional, false);
        $this->RegisterPropertyInteger($AirQuality, 0);
      }
  }
  public function ApplyChanges() {
      //Never delete this line!
      parent::ApplyChanges();
      //Setze Filter für ReceiveData
      $this->SetReceiveDataFilter(".*AirQualitySensor.*");
      $anzahl = $this->ReadPropertyInteger("Anzahl");
      $Devices = [];
      for($count = 1; $count-1 < $anzahl; $count++) {
        $Devices[$count]["DeviceName"] = $this->ReadPropertyString("DeviceName{$count}");
        $Devices[$count]["VOCDensity"] = $this->ReadPropertyInteger("VOCDensity{$count}");
        $Devices[$count]["AirQuality"] = $this->ReadPropertyInteger("AirQuality{$count}");
        $Devices[$count]["AirQualityOptional"] = $this->ReadPropertyBoolean("AirQualityOptional{$count}");
        $BufferNameVOCDensity = $Devices[$count]["DeviceName"]." VOCDensity";
        $BufferNameAirQuality = $Devices[$count]["DeviceName"]." AirQuality";
        //Alte Registrierungen auf Variablen Veränderung aufheben
        $UnregisterBufferIDs = [];
        array_push($UnregisterBufferIDs,$this->GetBuffer($BufferNameVOCDensity));
        array_push($UnregisterBufferIDs,$this->GetBuffer($BufferNameAirQuality));
        $this->UnregisterMessages($UnregisterBufferIDs, 10603);
        if ($Devices[$count]["DeviceName"] != "") {
          //Regestriere VOCDensity Variable auf Veränderungen
          $RegisterBufferIDs = [];
          array_push($RegisterBufferIDs,$Devices[$count]["VOCDensity"]);
          array_push($RegisterBufferIDs,$Devices[$count]["AirQuality"]);
          $this->RegisterMessages($RegisterBufferIDs, 10603);
          //Buffer mit den aktuellen Variablen IDs befüllen für VOCDensity und AirQuality
          $this->SetBuffer($BufferNameVOCDensity,$Devices[$count]["VOCDensity"]);
          $this->SetBuffer($BufferNameAirQuality,$Devices[$count]["AirQuality"]);
          $this->addAccessory($Devices[$count]["DeviceName"],$Devices[$count]["AirQualityOptional"]);
        } else {
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

        //Prüfen ob die SenderID gleich der VOCDensity oder AirQuality Variable ist, dann den aktuellen Wert an die Bridge senden
        if ($SenderID == $Device["VOCDensity"]) {
          $Characteristic = "VOCDensity";
          $data = $Data[0];
          $result = intval($data);
          $this->sendJSONToParent("setValue", $Characteristic, $DeviceName, $result);
          
            $wert = $result;
            if ( $wert >= 450 && $wert <= 600 ) {   $result= 1; }
            if ( $wert >= 601 && $wert <= 800 ) {   $result= 2; }
            if ( $wert >= 801 && $wert <= 1000 ) {   $result= 3; }
            if ( $wert >= 1001 && $wert <= 1500 ) {   $result= 4; }
            if ( $wert >= 1501 && $wert <= 2000 ) {   $result= 5; }
            if ( $wert >= 2101) {   $result= 5; }
            $VariableID = $this->ReadPropertyInteger($AirQualityCount);
            $variable = IPS_GetVariable($VariableID);
            $variableObject = IPS_GetObject($VariableID);
            //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
            $result = $this->ConvertVariable($variable, $result);
            //Geräte Variable setzen
//            if ($DummyOptionalValue == true) {
//              $this->SendDebug('setState Dummy CurrentPosition',$VariableID, 0);
              SetValue($VariableID, $result);
//            } else {
              IPS_RequestAction($variableObject["ParentID"], $variableObject['ObjectIdent'], $result);
//            }          
          
          
        }

        if ($SenderID == $Device["AirQuality"]) {
          $Characteristic = "AirQuality";
          $data = $Data[0];
          $this->sendJSONToParent("setValue", $Characteristic, $DeviceName, $result);
        }
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
      $form .= '{ "type": "Button", "label": "Löschen", "onClick": "echo HBAirQualitySensor_removeAccessory('.$this->InstanceID.','.$count.');" },';
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
      //Prüfen ob der übergebene Name aus dem Socket zu einem Namen aus der Konfirgurationsform passt
      $name = $Device["DeviceName"];
      if ($DeviceName == $name) {
        //IPS Variable abfragen
        switch ($Characteristic) {
          case 'VOCDensity':
            //AirQualitySensor VOCDensity abfragen
            $result = floatval(GetValue($Device["VOCDensity"]));
            $result = $Device["VOCDensityTrue"];
            break;
          case 'AirQuality':
            //AirQualitySensor AirQuality abfragen
            $result = GetValue($Device["AirQuality"]);
            $result = intval($result);
            break;
        }
        //Status an die Bridge senden
        $this->sendJSONToParent("callback", $Characteristic, $DeviceName, $result);
        return;
      }
    }
  }
  public function setVar($DeviceName, $value, $Characteristic) {
    $Devices = unserialize($this->getBuffer("AirQualitySensor Config"));
    $anzahl = $this->ReadPropertyInteger("Anzahl");
    for($count = 1; $count -1 < $anzahl; $count++) {
      $Device = $Devices[$count];
      //Prüfen ob der übergebene Name aus dem Hook zu einem Namen aus der Konfirgurationsform passt
      $name = $Device["DeviceName"];
      if ($DeviceName == $name) {
        switch ($Characteristic) {
          case 'VOCDensity':
             $variable = IPS_GetVariable($Device["VOCDensity"]);
             $variableObject = IPS_GetObject($Device["VOCDensity"]);
              //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
              $result = $this->ConvertVariable($variable, $value);
              //Geräte Variable setzen
              $this->SetValueToIPS($variable,$variableObject,$result);
            }
            break;
          case 'AirQuality':
            //Umrechnung
            $variable = IPS_GetVariable($Device["AirQuality"]);
            $variableObject = IPS_GetObject($Device["AirQuality"]);
            //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
            $result = $this->ConvertVariable($variable, $value);
            //Geräte Variable setzen
            $this->SetValueToIPS($variable,$variableObject,$result);
            break;
        }
      }
    }
  }
  private function addAccessory($DeviceName,$AirQuality) {
    //Payload bauen
    $payload["name"] = $DeviceName;
    $payload["service"] = "AirQualitySensor";

    $AirQuality["UNKNOWN "] = 0;
    $AirQuality["EXCELLENT"] = 1;
    $AirQuality["GOOD"] = 2;
    $AirQuality["FAIR"] = 3;
    $AirQuality["INFERIOR"] = 4;
    $AirQuality["POOR"] = 5;

    $VOCDensity["minValue"] = 0;
    $VOCDensity["maxValue"] = 5000;
    $VOCDensity["minStep"] = 1;

    $array["topic"] ="add";
    $payload["AirQuality"] = $AirQuality;
    $payload["VOCDensity"] = $VOCDensity;

    $array["payload"] = $payload;
    $data = json_encode($array);
    $SendData = json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Buffer" => $data));
    @$this->SendDataToParent($SendData);
  }
}
?>