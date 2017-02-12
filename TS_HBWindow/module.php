<?
class TS_HBWindow extends IPSModule {
/*
Service.Window = function(displayName, subtype) {
  Service.call(this, displayName, '0000008B-0000-1000-8000-0026BB765291', subtype);

  // Required Characteristics
  this.addCharacteristic(Characteristic.CurrentPosition);
  this.addCharacteristic(Characteristic.TargetPosition);
  this.addCharacteristic(Characteristic.PositionState);

  // Optional Characteristics
  this.addOptionalCharacteristic(Characteristic.HoldPosition);
  this.addOptionalCharacteristic(Characteristic.ObstructionDetected);
  this.addOptionalCharacteristic(Characteristic.Name);
  // The value property of PositionState must be one of the following:
Characteristic.PositionState.DECREASING = 0;
Characteristic.PositionState.INCREASING = 1;
Characteristic.PositionState.STOPPED = 2;
alles andere 0-100
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
        $WindowID = "WindowID{$count}";
        $WindowState = "WindowState{$count}";
        $WindowTarget = "WindowTarget{$count}";
        $WindowCurrent = "WindowCurrent{$count}";

        $WindowDummyOptional = "WindowDummyOptional{$count}";
        $this->RegisterPropertyString($DeviceName, "");
        $this->RegisterPropertyInteger($WindowID, 0);
        $this->RegisterPropertyInteger($WindowState, 0);
        $this->RegisterPropertyInteger($WindowTarget, 0);
        $this->RegisterPropertyInteger($WindowCurrent, 0);
        $this->RegisterPropertyBoolean($WindowDummyOptional, false);
        $this->SetBuffer($DeviceName." Window ".$WindowState,"");
      }
  }
  public function ApplyChanges() {
      //Never delete this line!
      parent::ApplyChanges();
      $this->ConnectParent("{86C2DE8C-FB21-44B3-937A-9B09BB66FB76}");
      $anzahl = $this->ReadPropertyInteger("Anzahl");

      for($count = 1; $count-1 < $anzahl; $count++) {
        $DeviceNameCount = "DeviceName{$count}";
        $WindowStateCount = "WindowState{$count}";
        $WindowTargetCount = "WindowTarget{$count}";
        $WindowCurrentCount = "WindowCurrent{$count}";

        $BufferNameState = $DeviceNameCount." state ".$WindowStateCount;
        $BufferNameTarget = $DeviceNameCount." Target ".$WindowTargetCount;
        $BufferNameCurrent = $DeviceNameCount." Current ".$WindowCurrentCount;

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
          $NewVariableID = $this->ReadPropertyInteger($WindowStateCount);
          $this->RegisterMessage($NewVariableID, 10603);

          //Regestriere Brightness Variable auf Veränderungen
          $NewVariableID = $this->ReadPropertyInteger($WindowTargetCount);
          $this->RegisterMessage($NewVariableID, 10603);

          $NewVariableID = $this->ReadPropertyInteger($WindowCurrentCount);
          $this->RegisterMessage($NewVariableID, 10603);

          //Buffer mit den aktuellen Variablen IDs befüllen für State und Brightness
          $this->SetBuffer($BufferNameState,$this->ReadPropertyInteger($WindowStateCount));
          $this->SetBuffer($BufferNameTarget,$this->ReadPropertyInteger($WindowTargetCount));
          $this->SetBuffer($BufferNameCurrent,$this->ReadPropertyInteger($WindowCurrentCount));

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
      $WindowStateCount = "WindowState{$count}";
      $WindowState = $this->ReadPropertyInteger($WindowStateCount);
      $WindowTarget = $this->ReadPropertyInteger($WindowTargetCount);
      $WindowCurrent = $this->ReadPropertyInteger($WindowCurrentCount);
      $data = $Data[0]; 
      //Prüfen ob die SenderID gleich der State Variable ist, dann den aktuellen Wert an die Bridge senden
      switch ($SenderID) {
       case $WindowState:
        $Characteristic = "TargetPosition";
//        $result = ($data) ? 'true' : 'false';
        $result = intval($data);
        $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
        $JSON['Buffer'] = utf8_encode('{"topic": "setValue", "Characteristic": "'.$Characteristic.'", "Device": "'.$DeviceName.'", "value": "'.$result.'"}');
        $Data = json_encode($JSON);
        $this->SendDataToParent($Data);
        break;
        case $WindowCurrent:
          $result = intval($data);
          $result = ($data) ? '"1"' : '"0"'; //
          $Characteristic ="CurrentPosition";
          $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
          $JSON['Buffer'] = utf8_encode('{"topic": "setValue", "Characteristic": "'.$Characteristic.'", "Device": "'.$DeviceName.'", "value": "'.$result.'"}');
          $Data = json_encode($JSON);
          $this->SendDataToParent($Data);
        break;
        case $WindowTarget:
          $result = intval($data);
          $result = "2";
          $Characteristic ="PositionState";
          $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
          $JSON['Buffer'] = utf8_encode('{"topic": "setValue", "Characteristic": "'.$Characteristic.'", "Device": "'.$DeviceName.'", "value": "'.$result.'"}');
          $Data = json_encode($JSON);
          $this->SendDataToParent($Data);
        brake;

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
      $form .= '{ "type": "SelectInstance", "name": "WindowID'.$count.'", "caption": "Gerät" },';
      $form .= '{ "type": "SelectVariable", "name": "WindowState'.$count.'", "caption": "Status (Characteristic .State )" },';
      $form .= '{ "type": "SelectVariable", "name": "WindowTarget'.$count.'", "caption": "Target (Characteristic .State )" },';
      $form .= '{ "type": "SelectVariable", "name": "WindowCurrent'.$count.'", "caption": "Current (Characteristic .State )" },';
      $form .= '{ "type": "Label", "label": "Soll eine eigene Variable geschaltet werden?" },';
      $form .= '{ "type": "CheckBox", "name": "WindowDummyOptional'.$count.'", "caption": "Ja" },';
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
    //Prüfen ob die ankommenden Daten für den Window sind wenn ja, Status abfragen oder setzen
    if ($HomebridgeData->Action == "get" && $HomebridgeData->Service == "Window") {
      $this->getState($HomebridgeData->Device, $HomebridgeData->Characteristic);
    }
    if ($HomebridgeData->Action == "set" && $HomebridgeData->Service == "Window") {
      $this->setState($HomebridgeData->Device, $HomebridgeData->Value, $HomebridgeData->Characteristic);
    }
  }

  public function getState($DeviceName, $Characteristic) {
    $anzahl = $this->ReadPropertyInteger("Anzahl");
//$this->SendDebug('Dummy ',$anzahl, 0);
    for($count = 1; $count -1 < $anzahl; $count++) {

      //Hochzählen der Konfirgurationsform Variablen
      $DeviceNameCount = "DeviceName{$count}";
      $WindowStateCount = "WindowState{$count}";
      $WindowTargetCount = "WindowTarget{$count}";
      $WindowCurrentCount = "WindowCurrent{$count}";
      //Prüfen ob der übergebene Name aus dem Hook zu einem Namen aus der Konfirgurationsform passt
      $name = $this->ReadPropertyString($DeviceNameCount);
//$this->SendDebug('Dummy ',$name, 0);
      if ($DeviceName == $name) {
  //IPS Variable abfragen
         switch ($Characteristic) {
          case 'CurrentPosition':
            //abfragen
            $VariableID = $this->ReadPropertyInteger($WindowCurrentCount);
            $result = GetValue($VariableID);
            //$result = ($result) ? 'true' : 'false';
            break;
          case 'TargetPosition':
            // abfragen
            $VariableID = $this->ReadPropertyInteger($WindowTargetCount);
            $result = GetValue($WindowTargetCount);
            break;
          case 'PositionState':
            // abfragen
            $VariableID = $this->ReadPropertyInteger($WindowStateCount);
            $result = GetValue($WindowStateCount);
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

  public function setState($DeviceName, $state, $variable) {
    $anzahl = $this->ReadPropertyInteger("Anzahl");

    for($count = 1; $count -1 < $anzahl; $count++) {

      //Hochzählen der Konfirgurationsform Variablen
      $DeviceNameCount = "DeviceName{$count}";
      $WindowStateCount = "WindowState{$count}";
      $WindowTargetCount = "WindowTarget{$count}";
      $WindowCurrentCount = "WindowCurrent{$count}";
      $DummyOptional = "WindowDummyOptional{$count}";
      //Prüfen ob der übergebene Name aus dem Hook zu einem Namen aus der Konfirgurationsform passt
      $name = $this->ReadPropertyString($DeviceNameCount);
      if ($DeviceName == $name) {
        $DummyOptionalValue = $this->ReadPropertyBoolean($DummyOptional);

        switch ($Characteristic) {
          case 'CurrentPosition':
            //Lightbulb State abfragen
            $VariableID = $this->ReadPropertyInteger($WindowTargetCount);
            $variable = IPS_GetVariable($VariableID);
            $variableObject = IPS_GetObject($VariableID);
            if ($SDummyOptionalValue == true) {
              $this->SendDebug('setState Dummy CurrentPosition',$VariableID, 0);
              SetValue($VariableID, $result);
            } else {
              IPS_RequestAction($variableObject["ParentID"], $variableObject['ObjectIdent'], $result);
            }
/*
            $result = ($result) ? 'true' : 'false';
            if ($result == true && $value == 0) {
              $variable = IPS_GetVariable($VariableStateID);
              $variableObject = IPS_GetObject($VariableStateID);
              //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
              $result = $this->ConvertVariable($variable, $value);
              //Geräte Variable setzen
              IPS_RequestAction($variableObject["ParentID"], $variableObject['ObjectIdent'], $result);
            }

            if ($result == "false" && $value == 1) {
              $variable = IPS_GetVariable($VariableStateID);
              $variableObject = IPS_GetObject($VariableStateID);
              //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
              $result = $this->ConvertVariable($variable, $value);
              //Geräte Variable setzen
              IPS_RequestAction($variableObject["ParentID"], $variableObject['ObjectIdent'], $result);
            }
*/
            break;
          case 'TargetPosition':
            //Lightbulb Brightness abfragen
            $VariableID = $this->ReadPropertyInteger($WindowTargetCount);
            $variable = IPS_GetVariable($VariableID);
            $variableObject = IPS_GetObject($VariableID);
            //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
            $result = $this->ConvertVariable($variable, $value);
            //Geräte Variable setzen
            if ($SDummyOptionalValue == true) {
              $this->SendDebug('setState Dummy CurrentPosition',$VariableID, 0);
              SetValue($VariableID, $result);
            } else {
              IPS_RequestAction($variableObject["ParentID"], $variableObject['ObjectIdent'], $result);
            }
            break;
          case 'PositionState':
            //Lightbulb Brightness abfragen
            $VariableID = $this->ReadPropertyInteger($WindowStateCount);
            $variable = IPS_GetVariable($VariableID);
            $variableObject = IPS_GetObject($VariableID);
            //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
            $result = $this->ConvertVariable($variable, $value);
            //Geräte Variable setzen
            if ($SDummyOptionalValue == true) {
              $this->SendDebug('setState Dummy CurrentPosition',$VariableID, 0);
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
    $payload["service"] = "Window";

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
