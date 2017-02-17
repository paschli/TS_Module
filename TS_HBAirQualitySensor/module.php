<?
class TS_HBAirQualitySensor extends IPSModule {
/*
>2100 	Sehr Schlecht (stark verschmutzte Raumluft; Lüftung erfolderlich)
2100 ... 1501 	Schlecht (stark verschmutzte Raumluft; Lüftung erforderlich)
1500 ... 1001 	Mittel (verschmutzte Raumluft; Lüftung empfohlen)
1000 ... 801 	Befriedigend
800 ... 601 	Gut
600 ... 450 	Hervorragend
*/
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

        $AirQualitySensorDummyOptional = "AirQualitySensorDummyOptional{$count}";
        $this->RegisterPropertyString($DeviceName, "");
        $this->RegisterPropertyInteger($AirQualitySensorID, 0);
        $this->RegisterPropertyInteger($VOCDensity, 0);
        $this->RegisterPropertyInteger($AirQuality, 0);
        $this->RegisterPropertyInteger($AirQualitySensorCurrent, 0);
        $this->RegisterPropertyBoolean($AirQualitySensorDummyOptional, false);
        $this->SetBuffer($DeviceName." AirQualitySensor ".$VOCDensity,"");
      }
  }
  public function ApplyChanges() {
      //Never delete this line!
      parent::ApplyChanges();
      $this->ConnectParent("{86C2DE8C-FB21-44B3-937A-9B09BB66FB76}");
      $anzahl = $this->ReadPropertyInteger("Anzahl");

      for($count = 1; $count-1 < $anzahl; $count++) {
        $DeviceNameCount = "DeviceName{$count}";
        $VOCDensityCount = "VOCDensity{$count}";
        $AirQualityCount = "AirQuality{$count}";
        $AirQualitySensorCurrentCount = "AirQualitySensorCurrent{$count}";

        $BufferNameState = $DeviceNameCount." state ".$VOCDensityCount;
        $BufferNameTarget = $DeviceNameCount." Target ".$AirQualityCount;
        $BufferNameCurrent = $DeviceNameCount." Current ".$AirQualitySensorCurrentCount;

        $VariableIDStateBuffer = $this->GetBuffer($BufferNameState);
        $VariableIDTargetBuffer = $this->GetBuffer($BufferNameTarget);
        $VariableIDCurrentBuffer = $this->GetBuffer($BufferNameCurrent);

        //Alte Registrierung auf Variablen Veränderung aufheben
        if (is_int($VariableIDStateBuffer)) {
          $this->UnregisterMessage(intval($VariableIDStateBuffer), 10603);
        }
        if (is_int($VariableIDTargetBuffer)) {
          $this->UnregisterMessage(intval($VariableIDTargetBuffer), 10603);
        }
        if (is_int($VariableIDCurrentBuffer)) {
          $this->UnregisterMessage(intval($VariableIDCurrentBuffer), 10603);
        }

        if ($this->ReadPropertyString($DeviceNameCount) != "") {
//          $BrightnessBoolean = $this->ReadPropertyBoolean($VariableBrightnessOptionalCount);

          //Regestriere State Variable auf Veränderungen
          $NewVariableID = $this->ReadPropertyInteger($VOCDensityCount);
          $this->RegisterMessage($NewVariableID, 10603);

          //Regestriere Brightness Variable auf Veränderungen
          $NewVariableID = $this->ReadPropertyInteger($AirQualityCount);
          $this->RegisterMessage($NewVariableID, 10603);

          $NewVariableID = $this->ReadPropertyInteger($AirQualitySensorCurrentCount);
          $this->RegisterMessage($NewVariableID, 10603);

          //Buffer mit den aktuellen Variablen IDs befüllen für State und Brightness
          $this->SetBuffer($BufferNameState,$this->ReadPropertyInteger($VOCDensityCount));
          $this->SetBuffer($BufferNameTarget,$this->ReadPropertyInteger($AirQualityCount));
          $this->SetBuffer($BufferNameCurrent,$this->ReadPropertyInteger($AirQualitySensorCurrentCount));

//          $this->addAccessory($this->ReadPropertyString($DeviceNameCount),$BrightnessBoolean);
          $this->addAccessory($this->ReadPropertyString($DeviceNameCount));
        } else {
          return;
        }
      }

    }

  public function Destroy() {
  }

  public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
    if ($Data[1] == true) {
    $anzahl = $this->ReadPropertyInteger("Anzahl");

    for($count = 1; $count-1 < $anzahl; $count++) {
      $DeviceNameCount = "DeviceName{$count}";
      $DeviceName = $this->ReadPropertyString($DeviceNameCount);
      $VOCDensityCount = "VOCDensity{$count}";
      $AirQualityCount= "AirQuality{$count}";
      $AirQualitySensorCurrentCount= "AirQualitySensorCurrent{$count}";      
      $VOCDensity = $this->ReadPropertyInteger($VOCDensityCount);
      $AirQuality = $this->ReadPropertyInteger($AirQualityCount);
      $AirQualitySensorCurrent = $this->ReadPropertyInteger($AirQualitySensorCurrentCount);
      $data = $Data[0]; 
      //Prüfen ob die SenderID gleich der State Variable ist, dann den aktuellen Wert an die Bridge senden
      switch ($SenderID) {
       case $VOCDensity:
        $Characteristic = "VOCDensity";
//        $result = ($data) ? 'true' : 'false';
        $result = intval($data);
        $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
        $JSON['Buffer'] = utf8_encode('{"topic": "setValue", "Characteristic": "'.$Characteristic.'", "Device": "'.$DeviceName.'", "value": "'.$result.'"}');
        $Data = json_encode($JSON);
        $this->SendDataToParent($Data);
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
            if ($DummyOptionalValue == true) {
//              $this->SendDebug('setState Dummy CurrentPosition',$VariableID, 0);
              SetValue($VariableID, $result);
            } else {
              IPS_RequestAction($variableObject["ParentID"], $variableObject['ObjectIdent'], $result);
            }
       
        
        break;
        case $AirQualitySensorCurrent:
          $result = intval($data);
//          $result = ($data) ? '"1"' : '"0"'; //
          $Characteristic ="CurrentPosition";
          $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
          $JSON['Buffer'] = utf8_encode('{"topic": "setValue", "Characteristic": "'.$Characteristic.'", "Device": "'.$DeviceName.'", "value": "'.$result.'"}');
          $Data = json_encode($JSON);
          $this->SendDataToParent($Data);
        break;
        case $AirQuality:
          $result = intval($data);
          $Characteristic ="AirQuality";      
          $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
          $JSON['Buffer'] = utf8_encode('{"topic": "setValue", "Characteristic": "'.$Characteristic.'", "Device": "'.$DeviceName.'", "value": "'.$result.'"}');
          $Data = json_encode($JSON);
          $this->SendDataToParent($Data);
        break;

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
      $form .= '{ "type": "SelectVariable", "name": "VOCDensity'.$count.'", "caption": "VOCDensity " },';
      $form .= '{ "type": "SelectVariable", "name": "AirQuality'.$count.'", "caption": "AirQuality " },';
      $form .= '{ "type": "Label", "label": "Soll eine eigene Variable geschaltet werden?" },';
      $form .= '{ "type": "CheckBox", "name": "AirQualitySensorDummyOptional'.$count.'", "caption": "Ja" },';
      $form .= '{ "type": "Button", "label": "Löschen", "onClick": "echo TSHBair_removeAccessory('.$this->InstanceID.','.$count.');" },';
      
      if ($count == $anzahl) {
        $form .= '{ "type": "Label", "label": "------------------" }';
      } else {
        $form .= '{ "type": "Label", "label": "------------------" },';
      }
    }
    $form .= ']}';
    return $form;
  }

  public function ReceiveData($JSONString) {
    $data = json_decode($JSONString);
    // Buffer decodieren und in eine Variable schreiben
    $Buffer = utf8_decode($data->Buffer);
    // Und Diese dann wieder dekodieren
    $HomebridgeData = json_decode($Buffer);
    //Prüfen ob die ankommenden Daten für den AirQualitySensor sind wenn ja, Status abfragen oder setzen
    if ($HomebridgeData->Action == "get" && $HomebridgeData->Service == "AirQualitySensor") {
      $this->getState($HomebridgeData->Device, $HomebridgeData->Characteristic);
    }
    if ($HomebridgeData->Action == "set" && $HomebridgeData->Service == "AirQualitySensor") {
      $this->setState($HomebridgeData->Device, $HomebridgeData->Value, $HomebridgeData->Characteristic);
    }
  }

  public function getState($DeviceName, $Characteristic) {
    $anzahl = $this->ReadPropertyInteger("Anzahl");
//$this->SendDebug('Dummy ',$anzahl, 0);
    for($count = 1; $count -1 < $anzahl; $count++) {

      //Hochzählen der Konfirgurationsform Variablen
      $DeviceNameCount = "DeviceName{$count}";
      $VOCDensityCount = "VOCDensity{$count}";
      $AirQualityCount = "AirQuality{$count}";

      //Prüfen ob der übergebene Name aus dem Hook zu einem Namen aus der Konfirgurationsform passt
      $name = $this->ReadPropertyString($DeviceNameCount);
//$this->SendDebug('Dummy ',$name, 0);
      if ($DeviceName == $name) {
  //IPS Variable abfragen
         switch ($Characteristic) {
          case 'AirQuality':
            // abfragen
            $VariableID = $this->ReadPropertyInteger($AirQualityCount);
            $result = GetValue($VariableID);
            break;
          case 'VOCDensity':
            // abfragen
            $VariableID = $this->ReadPropertyInteger($VOCDensityCount);
            $result = GetValue($VariableID);
            break;
            
        }
//        $result = ($result) ? 'true' : 'false';
        $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
        $JSON['Buffer'] = utf8_encode('{"topic": "callback", "Characteristic": "'.$Characteristic.'", "Device": "'.$DeviceName.'", "value": "'.$result.'"}');
        $Data = json_encode($JSON);
        $this->SendDataToParent($Data);

        return;
      }
    }
  }

  public function setState($DeviceName, $value, $Characteristic) {
    $anzahl = $this->ReadPropertyInteger("Anzahl");

    for($count = 1; $count -1 < $anzahl; $count++) {

      //Hochzählen der Konfirgurationsform Variablen
      $DeviceNameCount = "DeviceName{$count}";
      $VOCDensityCount = "VOCDensity{$count}";
      $AirQualityCount = "AirQuality{$count}";
      $AirQualitySensorCurrentCount = "AirQualitySensorCurrent{$count}";
      $DummyOptional = "AirQualitySensorDummyOptional{$count}";
      //Prüfen ob der übergebene Name aus dem Hook zu einem Namen aus der Konfirgurationsform passt
      $name = $this->ReadPropertyString($DeviceNameCount);
      if ($DeviceName == $name) {
        $DummyOptionalValue = $this->ReadPropertyBoolean($DummyOptional);

        switch ($Characteristic) {
          case 'AirQuality':
            //Lightbulb Brightness abfragen
            $VariableID = $this->ReadPropertyInteger($AirQualityCount);
            $variable = IPS_GetVariable($VariableID);
            $variableObject = IPS_GetObject($VariableID);
            //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
            $result = $this->ConvertVariable($variable, $value);
            //Geräte Variable setzen
            if ($DummyOptionalValue == true) {
//              $this->SendDebug('setState Dummy CurrentPosition',$VariableID, 0);
              SetValue($VariableID, $result);
            } else {
              IPS_RequestAction($variableObject["ParentID"], $variableObject['ObjectIdent'], $result);
            }
            break;
          case 'VOCDensity':
            //Lightbulb Brightness abfragen
            $VariableID = $this->ReadPropertyInteger($VOCDensityCount);
            $variable = IPS_GetVariable($VariableID);
            $variableObject = IPS_GetObject($VariableID);
            //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
            $result = $this->ConvertVariable($variable, $value);
            //Geräte Variable setzen
            if ($DummyOptionalValue == true) {
 //             $this->SendDebug('setState Dummy CurrentPosition',$VariableID, 0);
              SetValue($VariableID, $result);
            } else {
              IPS_RequestAction($variableObject["ParentID"], $variableObject['ObjectIdent'], $result);
            }
            break;

        }
      }
    }
  }

  private function addAccessory($DeviceName) {
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
  public function removeAccessory($DeviceCount) {
    //Payload bauen
    $DeviceName = $this->ReadPropertyString("DeviceName{$DeviceCount}");
    $payload["name"] = $DeviceName;

    $array["topic"] ="remove";
    $array["payload"] = $payload;
    $data = json_encode($array);
    $SendData = json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Buffer" => $data));
    $this->SendDebug('Remove',$SendData,0);
    $this->SendDataToParent($SendData);
    return "Gelöscht!";
  }

  public function ConvertVariable($variable, $state) {
      switch ($variable["VariableType"]) {
        case 0: // boolean
          return boolval($state);
        case 1: // integer
          return intval($state);
        case 2: // float
          return floatval($state);
        case 3: // string
          return strval($state);
    }
  }
}
?>
