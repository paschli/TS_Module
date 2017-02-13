<?
/*
  Characteristic.call(this, 'Current Ambient Light Level', '0000006B-0000-1000-8000-0026BB765291');
    unit: Characteristic.Units.LUX,
    maxValue: 100000,
    minValue: 0.0001,
    minStep: 0.0001,

LightSensor

*/

class TS_HBLightSensor extends IPSModule {

  public function Create() {
      //Never delete this line!
      parent::Create();
      $this->ConnectParent("{86C2DE8C-FB21-44B3-937A-9B09BB66FB76}");
      //Anzahl die in der Konfirgurationsform angezeigt wird - Hier Standard auf 1
      $this->RegisterPropertyInteger("Anzahl",1);
      //99 Geräte können pro Konfirgurationsform angelegt werden
      for($count = 1; $count -1 < 99; $count++) {
        $DeviceName = "DeviceName{$count}";
        $LightSensorID = "LightSensorID{$count}";
        $CurrentAmbientLightLevel = "CurrentAmbientLightLevel{$count}";
        $LightSensorDummyOptional = "LightSensorDummyOptional{$count}";

        $this->RegisterPropertyString($DeviceName, "");
        $this->RegisterPropertyInteger($LightSensorID, 0);
        $this->RegisterPropertyFloat($CurrentAmbientLightLevel, 0);
        $this->RegisterPropertyBoolean($LightSensorDummyOptional, false);
        $this->SetBuffer($DeviceName." LightSensor ".$CurrentAmbientLightLevel,"");
      }
  }
  public function ApplyChanges() {
      //Never delete this line!
      parent::ApplyChanges();
      $this->ConnectParent("{86C2DE8C-FB21-44B3-937A-9B09BB66FB76}");
      $anzahl = $this->ReadPropertyInteger("Anzahl");

      for($count = 1; $count-1 < $anzahl; $count++) {
        $DeviceNameCount = "DeviceName{$count}";
        $CurrentAmbientLightLevelCount = "CurrentAmbientLightLevel{$count}";
        $BufferName = $DeviceNameCount." State ".$CurrentAmbientLightLevelCount;

        $CurrentAmbientLightLevelBuffer = $this->GetBuffer($BufferName);

        if (is_int($CurrentAmbientLightLevelBuffer)) {
        $this->UnregisterMessage(intval($CurrentAmbientLightLevelBuffer), 10603);
        }
        $DeviceName = $this->ReadPropertyString($DeviceNameCount);
        if ($DeviceName != "") {
          $CurrentAmbientLightLevelID = $this->ReadPropertyInteger($CurrentAmbientLightLevelCount);
          $this->RegisterMessage($CurrentAmbientLightLevelID, 10603);
          $this->SetBuffer($BufferName,$CurrentAmbientLightLevelID);
          $this->addAccessory($DeviceName);
        }
        else {
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
      $CurrentAmbientLightLevelCount = "CurrentAmbientLightLevel{$count}";
      $CurrentAmbientLightLevel = $this->ReadPropertyInteger($CurrentAmbientLightLevelCount);
      
      //Prüfen ob die SenderID gleich der State Variable ist, dann den aktuellen Wert an die Bridge senden
      if ($CurrentAmbientLightLevel == $SenderID) {
        $DeviceName = $this->ReadPropertyString($DeviceNameCount);
        $Characteristic = "CurrentAmbientLightLevel";
        $data = $Data[0];
        $result = intval($data);
        $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
        $JSON['Buffer'] = utf8_encode('{"topic": "setValue", "Characteristic": "'.$Characteristic.'", "Device": "'.$DeviceName.'", "value": "'.$result.'"}');
        $Data = json_encode($JSON);
        $this->SendDataToParent($Data);
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
      $form .= '{ "type": "SelectInstance", "name": "LightSensorID'.$count.'", "caption": "Gerät" },';
      $form .= '{ "type": "SelectVariable", "name": "CurrentAmbientLightLevel'.$count.'", "caption": "Status (Characteristic .CurrentAmbientLightLevel )" },';
      $form .= '{ "type": "Label", "label": "Soll eine eigene Variable geschaltet werden?" },';
      $form .= '{ "type": "CheckBox", "name": "LightSensorDummyOptional'.$count.'", "caption": "Ja" },';
      $form .= '{ "type": "Label", "label": "LightSensor invertieren ?" },';
      $form .= '{ "type": "Button", "label": "Löschen", "onClick": "echo TSHBLig_removeAccessory('.$this->InstanceID.','.$count.');" },';

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
    //Prüfen ob die ankommenden Daten für den LightSensor sind wenn ja, Status abfragen oder setzen
    if ($HomebridgeData->Action == "get" && $HomebridgeData->Service == "LightSensor") {
      $this->getState($HomebridgeData->Device, $HomebridgeData->Characteristic);
    }
    if ($HomebridgeData->Action == "set" && $HomebridgeData->Service == "LightSensor") {
      $this->setState($HomebridgeData->Device, $HomebridgeData->Value, $HomebridgeData->Characteristic);
    }
  }

  public function getState($DeviceName, $Characteristic) {
    $anzahl = $this->ReadPropertyInteger("Anzahl");

    for($count = 1; $count -1 < $anzahl; $count++) {

      //Hochzählen der Konfirgurationsform Variablen
      $DeviceNameCount = "DeviceName{$count}";
      $CurrentAmbientLightLevelCount = "CurrentAmbientLightLevel{$count}";
      //Prüfen ob der übergebene Name aus dem Hook zu einem Namen aus der Konfirgurationsform passt
      $name = $this->ReadPropertyString($DeviceNameCount);
      if ($DeviceName == $name) {
        //IPS Variable abfragen
        $CurrentAmbientLightLevelID = $this->ReadPropertyInteger($CurrentAmbientLightLevelCount);
        $result = intval(GetValue($CurrentAmbientLightLevelID));

        $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
        $JSON['Buffer'] = utf8_encode('{"topic": "callback", "Characteristic": "'.$Characteristic.'", "Device": "'.$DeviceName.'", "value": "'.$result.'"}');
        $Data = json_encode($JSON);
        $this->SendDataToParent($Data);
        return;
      }
    }
  }

  public function setState($DeviceName, $state, $variable) {
    $anzahl = $this->ReadPropertyInteger("Anzahl");

    for($count = 1; $count -1 < $anzahl; $count++) {

      //Hochzählen der Konfirgurationsform Variablen
      $DeviceNameCount = "DeviceName{$count}";
      $CurrentAmbientLightLevelCount = "CurrentAmbientLightLevel{$count}";
      $LightSensorDummyOptional = "LightSensorDummyOptional{$count}";
      //Prüfen ob der übergebene Name aus dem Hook zu einem Namen aus der Konfirgurationsform passt
      $name = $this->ReadPropertyString($DeviceNameCount);
      if ($DeviceName == $name) {
        $CurrentAmbientLightLevelID = $this->ReadPropertyInteger($CurrentAmbientLightLevelCount);
        $variable = IPS_GetVariable($CurrentAmbientLightLevelID);
        //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
        $result = $this->ConvertVariable($variable, $state);
        $variableObject = IPS_GetObject($CurrentAmbientLightLevelID);
        //Geräte Variable setzen
        $LightSensorDummyOptionalValue = $this->ReadPropertyBoolean($LightSensorDummyOptional);
        if ($LightSensorDummyOptionalValue == true) {
          $this->SendDebug('setState Light',$CurrentAmbientLightLevelID, 0);
          SetValue($CurrentAmbientLightLevelID, $result);
        } else {
          IPS_RequestAction($variableObject["ParentID"], $variableObject['ObjectIdent'], $result);
        }
      }
    }
  }

  private function addAccessory($DeviceName) {
    //Payload bauen
    $payload["name"] = $DeviceName;
    $payload["service"] = "LightSensor";

    $array["topic"] ="add";
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
