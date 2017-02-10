<?
class TS_HBGarage extends IPSModule {

  public function Create() {
      //Never delete this line!
      parent::Create();
      $this->ConnectParent("{86C2DE8C-FB21-44B3-937A-9B09BB66FB76}");
      //Anzahl die in der Konfirgurationsform angezeigt wird - Hier Standard auf 1
      $this->RegisterPropertyInteger("Anzahl",1);
      //99 Geräte können pro Konfirgurationsform angelegt werden
      for($count = 1; $count -1 < 99; $count++) {
        $DeviceName = "DeviceName{$count}";
        $GarageID = "GarageID{$count}";
        $GarageState = "GarageState{$count}";
        $GarageDummyOptional = "GarageDummyOptional{$count}";
        $this->RegisterPropertyString($DeviceName, "");
        $this->RegisterPropertyInteger($GarageID, 0);
        $this->RegisterPropertyInteger($GarageState, 0);
        $this->RegisterPropertyBoolean($GarageDummyOptional, false);
        $this->SetBuffer($DeviceName." Garage ".$GarageState,"");
      }
  }
  public function ApplyChanges() {
      //Never delete this line!
      parent::ApplyChanges();
      $this->ConnectParent("{86C2DE8C-FB21-44B3-937A-9B09BB66FB76}");
      $anzahl = $this->ReadPropertyInteger("Anzahl");

      for($count = 1; $count-1 < $anzahl; $count++) {
        $DeviceNameCount = "DeviceName{$count}";
        $GarageStateCount = "GarageState{$count}";
        $BufferName = $DeviceNameCount." State ".$GarageStateCount;

        $GarageStateBuffer = $this->GetBuffer($BufferName);

        if (is_int($GarageStateBuffer)) {
        $this->UnregisterMessage(intval($GarageStateBuffer), 10603);
        }
        $DeviceName = $this->ReadPropertyString($DeviceNameCount);
        if ($DeviceName != "") {
          $GarageStateID = $this->ReadPropertyInteger($GarageStateCount);
          $this->RegisterMessage($GarageStateID, 10603);
          $this->SetBuffer($BufferName,$GarageStateID);
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
    $anzahl = $this->ReadPropertyInteger("Anzahl");

    for($count = 1; $count-1 < $anzahl; $count++) {
      $DeviceNameCount = "DeviceName{$count}";
      $GarageStateCount = "GarageState{$count}";
      $GarageState = $this->ReadPropertyInteger($GarageStateCount);
      //Prüfen ob die SenderID gleich der State Variable ist, dann den aktuellen Wert an die Bridge senden
      if ($GarageState == $SenderID) {
        $DeviceName = $this->ReadPropertyString($DeviceNameCount);
        $Characteristic = "TargetDoorState";
        $data = $Data[0];
//        $result = ($data) ? 'true' : 'false';
        $result = intval($data);
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
      $form .= '{ "type": "ValidationTextBox", "name": "DeviceName'.$count.'", "caption": "Gerätename für die Homebridge" },';
      $form .= '{ "type": "SelectInstance", "name": "GarageID'.$count.'", "caption": "Gerät" },';
      $form .= '{ "type": "SelectVariable", "name": "GarageState'.$count.'", "caption": "Status (Characteristic .State )" },';
      $form .= '{ "type": "Label", "label": "Soll eine eigene Variable geschaltet werden?" },';
      $form .= '{ "type": "CheckBox", "name": "GarageDummyOptional'.$count.'", "caption": "Ja" },';
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
    //Prüfen ob die ankommenden Daten für den Garage sind wenn ja, Status abfragen oder setzen
    if ($HomebridgeData->Action == "get" && $HomebridgeData->Service == "GarageDoorOpener") {
      $this->getState($HomebridgeData->Device, $HomebridgeData->Characteristic);
    }
    if ($HomebridgeData->Action == "set" && $HomebridgeData->Service == "GarageDoorOpener") {
      $this->setState($HomebridgeData->Device, $HomebridgeData->Value, $HomebridgeData->Characteristic);
    }
  }
/*
Characteristic.CurrentDoorState.OPEN = 0;
Characteristic.CurrentDoorState.CLOSED = 1;
Characteristic.CurrentDoorState.OPENING = 2;
Characteristic.CurrentDoorState.CLOSING = 3;
Characteristic.CurrentDoorState.STOPPED = 4;
TargetDoorState
*/
  public function getState($DeviceName, $Characteristic) {
    $anzahl = $this->ReadPropertyInteger("Anzahl");

    for($count = 1; $count -1 < $anzahl; $count++) {

      //Hochzählen der Konfirgurationsform Variablen
      $DeviceNameCount = "DeviceName{$count}";
      $GarageStateCount = "GarageState{$count}";
      //Prüfen ob der übergebene Name aus dem Hook zu einem Namen aus der Konfirgurationsform passt
      $name = $this->ReadPropertyString($DeviceNameCount);
      if ($DeviceName == $name) {
        //IPS Variable abfragen
        $GarageStateID = $this->ReadPropertyInteger($GarageStateCount);
        $result = intval(GetValue($GarageStateID));
//        $result = ($result) ? 'true' : 'false';
        $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
        $JSON['Buffer'] = utf8_encode('{"topic": "callback", "Characteristic": "'.$Characteristic.'", "Device": "'.$DeviceName.'", "value": "'.$result.'"}');
        $Data = json_encode($JSON);
        $this->SendDataToParent($Data);
      
        if ($result == 1 ) {
          $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
          $JSON['Buffer'] = utf8_encode('{"topic": "setValue", "Characteristic": "CurrentPosition", "Device": "'.$DeviceName.'", "value": "100"}');
          $Data = json_encode($JSON);
          $this->SendDataToParent($Data);
        }
        if ($result == 0 ) {
          $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
          $JSON['Buffer'] = utf8_encode('{"topic": "setValue", "Characteristic": "CurrentPosition", "Device": "'.$DeviceName.'", "value": "0"}');
          $Data = json_encode($JSON);
          $this->SendDataToParent($Data);
        }
        
        return;
      }
    }
  }

  public function setState($DeviceName, $state, $variable) {
    $anzahl = $this->ReadPropertyInteger("Anzahl");

    for($count = 1; $count -1 < $anzahl; $count++) {

      //Hochzählen der Konfirgurationsform Variablen
      $DeviceNameCount = "DeviceName{$count}";
      $GarageStateCount = "GarageState{$count}";
      $GarageDummyOptional = "GarageDummyOptional{$count}";
      //Prüfen ob der übergebene Name aus dem Hook zu einem Namen aus der Konfirgurationsform passt
      $name = $this->ReadPropertyString($DeviceNameCount);
      if ($DeviceName == $name) {
        $GarageStateID = $this->ReadPropertyInteger($GarageStateCount);
        $variable = IPS_GetVariable($GarageStateID);
        //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
        $result = $this->ConvertVariable($variable, $state);
        $variableObject = IPS_GetObject($GarageStateID);
        //Geräte Variable setzen
        $GarageDummyOptionalValue = $this->ReadPropertyBoolean($GarageDummyOptional);
        if ($GarageDummyOptionalValue == true) {
          $this->SendDebug('setState Dummy',$GarageStateID, 0);
          SetValue($GarageStateID, $result);
        } else {
          IPS_RequestAction($variableObject["ParentID"], $variableObject['ObjectIdent'], $result);
        }
      }
    }
  }

  private function addAccessory($DeviceName) {
    //Payload bauen
    $payload["name"] = $DeviceName;
    $payload["service"] = "GarageDoorOpener";

    $array["topic"] ="add";
    $array["payload"] = $payload;
    $data = json_encode($array);
    $SendData = json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Buffer" => $data));
    @$this->SendDataToParent($SendData);
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
