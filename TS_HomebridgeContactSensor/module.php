<?
class TS_HomebridgeContactSensor extends IPSModule {
  public function Create() {
      //Never delete this line!
      parent::Create();
      $this->ConnectParent("{86C2DE8C-FB21-44B3-937A-9B09BB66FB76}");
        $this->RegisterPropertyInteger("Anzahl",1);

      for($count = 1; $count -1 < 99; $count++) {
        $DeviceName = "DeviceName{$count}";
        $SwitchID = "ContactID{$count}";
        $VariableState = "VariableState{$count}";
        $this->RegisterPropertyString($DeviceName, "");
        $this->RegisterPropertyInteger($ContactID, 0);
        $this->RegisterPropertyInteger($VariableState, 0);
        $this->SetBuffer($DeviceName." ContactSensor ".$VariableState,"");
      }
  }
  public function ApplyChanges() {
      //Never delete this line!
      parent::ApplyChanges();
      $anzahl = $this->ReadPropertyInteger("Anzahl");
      for($count = 1; $count-1 < $anzahl; $count++) {
        $DeviceNameID = "DeviceName{$count}";
        $VariableState = "VariableState{$count}";
        if (is_int($this->GetBuffer($DeviceNameID." ContactSensor ".$VariableState))) {
        $this->UnregisterMessage(intval($this->GetBuffer($DeviceNameID." ContactSensor ".$VariableState)), 10603);
        }
        if ($this->ReadPropertyString($DeviceNameID) != "") {
          $this->addAccessory($this->ReadPropertyString($DeviceNameID));
          $this->RegisterMessage($this->ReadPropertyInteger($VariableState), 10603);
          $this->SetBuffer($DeviceNameID." ContactSensor ".$VariableState,$this->ReadPropertyInteger($VariableState));
        }
        else {
          return;
        }
      }
    }

  public function Destroy() {
  }

  public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
    $anzahl = $this->ReadPropertyInteger("Anzahl");
    for($count = 1; $count-1 < $anzahl; $count++) {
      $VariableState = $this->ReadPropertyInteger("VariableState{$count}");
      if ($VariableState == $SenderID) {
        $DeviceName = $this->ReadPropertyString("DeviceName{$count}");
        $Characteristic = "ContactSensorState";
        $data = $Data[0];
        $result = ($data) ? 'true' : 'false';
        $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
        $JSON['Buffer'] = utf8_encode('{"topic": "setValue", "Characteristic": "'.$Characteristic.'", "Device": "'.$DeviceName.'", "value": "'.$result.'"}');
        $Data = json_encode($JSON);
        $this->SendDataToParent($Data);
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
      $form .= '{ "type": "ValidationTextBox", "name": "DeviceName'.$count.'", "caption": "HB Gerätename" },';
      $form .= '{ "type": "SelectInstance", "name": "ContactID'.$count.'", "caption": "Gerät" },';
      $form .= '{ "type": "SelectVariable", "name": "VariableState'.$count.'", "caption": "Status (Characteristic .ContactSensorState)" },';
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

      if ($HomebridgeData->Action == "get" && $HomebridgeData->Service == "ContactSensor") {
        $this->getState($HomebridgeData->Device, $HomebridgeData->Characteristic);
      }
      if ($HomebridgeData->Action == "set" && $HomebridgeData->Service == "ContactSensor") {
        $this->setState($HomebridgeData->Device, $HomebridgeData->Value, $HomebridgeData->Characteristic);
      }
  }

  public function getState($DeviceName, $Characteristic) {
    for($count = 1; $count -1 < $this->ReadPropertyInteger("Anzahl"); $count++) {
      //Hochzählen der Konfirgurationsform Variablen
      $ContactID = "ContactID{$count}";
      $VariableState = "VariableState{$count}";
      //Prüfen ob der übergebene Name aus dem Hook zu einem Namen aus der Konfirgurationsform passt
      if ($DeviceName == $this->ReadPropertyString("DeviceName{$count}")) {
        //IPS Variable abfragen
        $result1 = GetValue($this->ReadPropertyInteger("$VariableState"));
        //$result = ($result1) ? 'true' : 'false';
        $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
        $JSON['Buffer'] = utf8_encode('{"topic": "callback", "Characteristic": "'.$Characteristic.'", "Device": "'.$DeviceName.'", "value": "'.$result.'"}');
        $Data = json_encode($JSON);
        $this->SendDataToParent($Data);
        //$this->SendDataToParent(json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}","topic" => "setValue", "Characteristic" => , "Device" => $DeviceName, "value" => $result)));
        return;
      }
    }
  }

  public function setState($DeviceName, $state, $variable) {
    for($count = 1; $count -1 < $this->ReadPropertyInteger("Anzahl"); $count++) {
      //Hochzählen der Konfirgurationsform Variablen
      $ContactID = "ContactID{$count}";
      $VariableState = "VariableState{$count}";
      //Prüfen ob der übergebene Name aus dem Hook zu einem Namen aus der Konfirgurationsform passt
      if ($DeviceName == $this->ReadPropertyString("DeviceName{$count}")) {
        $variable = IPS_GetVariable($this->ReadPropertyInteger("VariableState{$count}"));
        //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
        $result = $this->ConvertVariable($variable, $state);
        $variableObject = IPS_GetObject($this->ReadPropertyInteger("VariableState{$count}"));
        //Geräte Variable setzen
        IPS_RequestAction($variableObject["ParentID"], $variableObject['ObjectIdent'], $result);
      }
    }
  }

  private function addAccessory($DeviceName) {
    $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
    $JSON['Buffer'] = utf8_encode('{"topic": "add", "name": "'.$DeviceName.'", "service": "ContactSensor"}');
    $Data = json_encode($JSON);
    $this->SendDataToParent($Data);
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