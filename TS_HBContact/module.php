<?
class TS_HBContact extends IPSModule {

  public function Create() {
      //Never delete this line!
      parent::Create();
      $this->ConnectParent("{86C2DE8C-FB21-44B3-937A-9B09BB66FB76}");
      //Anzahl die in der Konfirgurationsform angezeigt wird - Hier Standard auf 1
      $this->RegisterPropertyInteger("Anzahl",1);
      //99 Geräte können pro Konfirgurationsform angelegt werden
      for($count = 1; $count -1 < 99; $count++) {
        $DeviceName = "DeviceName{$count}";
        $ContaktID = "ContaktID{$count}";
        $ContactState = "ContactState{$count}";
        $ContactDummyOptional = "ContactDummyOptional{$count}";
        $this->RegisterPropertyString($DeviceName, "");
        $this->RegisterPropertyInteger($ContaktID, 0);
        $this->RegisterPropertyInteger($ContactState, 0);
        $this->RegisterPropertyBoolean($ContactDummyOptional, false);
        $this->SetBuffer($DeviceName." Contact ".$ContactState,"");
      }
  }
  public function ApplyChanges() {
      //Never delete this line!
      parent::ApplyChanges();
      $this->ConnectParent("{86C2DE8C-FB21-44B3-937A-9B09BB66FB76}");
      $anzahl = $this->ReadPropertyInteger("Anzahl");

      for($count = 1; $count-1 < $anzahl; $count++) {
        $DeviceNameCount = "DeviceName{$count}";
        $ContactStateCount = "ContactState{$count}";
        $BufferName = $DeviceNameCount." State ".$ContactStateCount;

        $ContactStateBuffer = $this->GetBuffer($BufferName);

        if (is_int($ContactStateBuffer)) {
        $this->UnregisterMessage(intval($ContactStateBuffer), 10603);
        }
        $DeviceName = $this->ReadPropertyString($DeviceNameCount);
        if ($DeviceName != "") {
          $ContactStateID = $this->ReadPropertyInteger($ContactStateCount);
          $this->RegisterMessage($ContactStateID, 10603);
          $this->SetBuffer($BufferName,$ContactStateID);
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
      $ContactStateCount = "ContactState{$count}";
      $ContactState = $this->ReadPropertyInteger($ContactStateCount);
      //Prüfen ob die SenderID gleich der State Variable ist, dann den aktuellen Wert an die Bridge senden
      if ($ContactState == $SenderID) {
        $DeviceName = $this->ReadPropertyString($DeviceNameCount);
        $Characteristic = "ContactSensorState ";
        $data = $Data[0];
        //$result = ($data) ? 'true' : 'false';
        $result = ($data);
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
      $form .= '{ "type": "SelectInstance", "name": "ContaktID'.$count.'", "caption": "Gerät" },';
      $form .= '{ "type": "SelectVariable", "name": "ContactState'.$count.'", "caption": "Status (Characteristic .ContactSensorState )" },';
      $form .= '{ "type": "Label", "label": "Soll eine eigene Variable geschaltet werden?" },';
      $form .= '{ "type": "CheckBox", "name": "ContactDummyOptional'.$count.'", "caption": "Ja" },';
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
    //Prüfen ob die ankommenden Daten für den Contact sind wenn ja, Status abfragen oder setzen
    if ($HomebridgeData->Action == "get" && $HomebridgeData->Service == "Contact") {
      $this->getState($HomebridgeData->Device, $HomebridgeData->Characteristic);
    }
    if ($HomebridgeData->Action == "set" && $HomebridgeData->Service == "Contact") {
      $this->setState($HomebridgeData->Device, $HomebridgeData->Value, $HomebridgeData->Characteristic);
    }
  }

  public function getState($DeviceName, $Characteristic) {
    $anzahl = $this->ReadPropertyInteger("Anzahl");

    for($count = 1; $count -1 < $anzahl; $count++) {

      //Hochzählen der Konfirgurationsform Variablen
      $DeviceNameCount = "DeviceName{$count}";
      $ContactStateCount = "ContactState{$count}";
      //Prüfen ob der übergebene Name aus dem Hook zu einem Namen aus der Konfirgurationsform passt
      $name = $this->ReadPropertyString($DeviceNameCount);
      if ($DeviceName == $name) {
        //IPS Variable abfragen
        $ContactStateID = $this->ReadPropertyInteger($ContactStateCount);
        $result = GetValue($ContactStateID);
        //$result = ($result) ? 'true' : 'false';
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
      $ContactStateCount = "ContactState{$count}";
      $ContactDummyOptional = "ContactDummyOptional{$count}";
      //Prüfen ob der übergebene Name aus dem Hook zu einem Namen aus der Konfirgurationsform passt
      $name = $this->ReadPropertyString($DeviceNameCount);
      if ($DeviceName == $name) {
        $ContactStateID = $this->ReadPropertyInteger($ContactStateCount);
        $variable = IPS_GetVariable($ContactStateID);
        //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
        $result = $this->ConvertVariable($variable, $state);
        $variableObject = IPS_GetObject($ContactStateID);
        //Geräte Variable setzen
        $ContactDummyOptionalValue = $this->ReadPropertyBoolean($ContactDummyOptional);
        if ($ContactDummyOptionalValue == true) {
          $this->SendDebug('setState Dummy',$ContactStateID, 0);
          SetValue($ContactStateID, $result);
        } else {
          IPS_RequestAction($variableObject["ParentID"], $variableObject['ObjectIdent'], $result);
        }
      }
    }
  }

  private function addAccessory($DeviceName) {
    $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
    $JSON['Buffer'] = utf8_encode('{"topic": "add", "name": "'.$DeviceName.'", "service": "ContactSensor"}');
    $Data = json_encode($JSON);
    @$this->SendDataToParent($Data);
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
