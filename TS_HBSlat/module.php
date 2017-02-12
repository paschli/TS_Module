<?
class TS_HBSlat  extends IPSModule {
/*
Service.Slat = function(displayName, subtype) {
  Service.call(this, displayName, '000000B9-0000-1000-8000-0026BB765291', subtype);

  // Required Characteristics
  this.addCharacteristic(Characteristic.SlatType);
  this.addCharacteristic(Characteristic.CurrentSlatState);

  // Optional Characteristics
  this.addOptionalCharacteristic(Characteristic.Name);
  this.addOptionalCharacteristic(Characteristic.CurrentTiltAngle);
  this.addOptionalCharacteristic(Characteristic.TargetTiltAngle);
  this.addOptionalCharacteristic(Characteristic.SwingMode);

Characteristic.TargetSlatState.MANUAL = 0;
Characteristic.TargetSlatState.AUTO = 1;
Characteristic.SlatType.HORIZONTAL = 0;
Characteristic.SlatType.VERTICAL = 1;
Characteristic.CurrentSlatState.FIXED = 0;
Characteristic.CurrentSlatState.JAMMED = 1;
Characteristic.CurrentSlatState.SWINGING = 2;

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
        $SlatID = "SlatID{$count}";
        $SlatType = "SlatType{$count}";
        $CurrentSlatState = "CurrentSlatState{$count}";
        $SwingMode = "SwingMode{$count}";

        $SlatDummyOptional = "SlatDummyOptional{$count}";
        $this->RegisterPropertyString($DeviceName, "");
        $this->RegisterPropertyInteger($SlatID, 0);
        $this->RegisterPropertyInteger($SlatType, 0);
        $this->RegisterPropertyInteger($CurrentSlatState, 0);
        $this->RegisterPropertyInteger($SwingMode, 0);
        $this->RegisterPropertyBoolean($SlatDummyOptional, false);
        $this->SetBuffer($DeviceName." Slat ".$SlatType,"");
      }
  }
  public function ApplyChanges() {
      //Never delete this line!
      parent::ApplyChanges();
      $this->ConnectParent("{86C2DE8C-FB21-44B3-937A-9B09BB66FB76}");
      $anzahl = $this->ReadPropertyInteger("Anzahl");

      for($count = 1; $count-1 < $anzahl; $count++) {
        $DeviceNameCount = "DeviceName{$count}";
        $SlatTypeCount = "SlatType{$count}";
        $CurrentSlatStateCount = "CurrentSlatState{$count}";
        $SwingModeCount = "SwingMode{$count}";

        $BufferNameState = $DeviceNameCount." SlatType ".$SlatTypeCount;
        $BufferNameTarget = $DeviceNameCount." CurrentSlatState ".$CurrentSlatStateCount;
        $BufferNameCurrent = $DeviceNameCount." SwingMode ".$SwingModeCount;

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
          $NewVariableID = $this->ReadPropertyInteger($SlatTypeCount);
          $this->RegisterMessage($NewVariableID, 10603);

          //Regestriere Brightness Variable auf Veränderungen
          $NewVariableID = $this->ReadPropertyInteger($CurrentSlatStateCount);
          $this->RegisterMessage($NewVariableID, 10603);

          $NewVariableID = $this->ReadPropertyInteger($SwingModeCount);
          $this->RegisterMessage($NewVariableID, 10603);

          //Buffer mit den aktuellen Variablen IDs befüllen für State und Brightness
          $this->SetBuffer($BufferNameState,$this->ReadPropertyInteger($SlatTypeCount));
          $this->SetBuffer($BufferNameTarget,$this->ReadPropertyInteger($CurrentSlatStateCount));
          $this->SetBuffer($BufferNameCurrent,$this->ReadPropertyInteger($SwingModeCount));

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
      $SlatTypeCount = "SlatType{$count}";
      $CurrentSlatStateCount= "CurrentSlatState{$count}";
      $SwingModeCount= "SwingMode{$count}";      
      $SlatType = $this->ReadPropertyInteger($SlatTypeCount);
      $CurrentSlatState = $this->ReadPropertyInteger($CurrentSlatStateCount);
      $SwingMode = $this->ReadPropertyInteger($SwingModeCount);
      $data = $Data[0]; 
      //Prüfen ob die SenderID gleich der State Variable ist, dann den aktuellen Wert an die Bridge senden
      switch ($SenderID) {
       case $SlatType:
        $Characteristic = "SlatType";
//        $result = ($data) ? 'true' : 'false';
        $result = intval($data);
        $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
        $JSON['Buffer'] = utf8_encode('{"topic": "setValue", "Characteristic": "'.$Characteristic.'", "Device": "'.$DeviceName.'", "value": "'.$result.'"}');
        $Data = json_encode($JSON);
        $this->SendDataToParent($Data);
        break;
        case $SwingMode:
          $result = intval($data);
//          $result = ($data) ? '"1"' : '"0"'; //
          $Characteristic ="SwingMode";
          $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
          $JSON['Buffer'] = utf8_encode('{"topic": "setValue", "Characteristic": "'.$Characteristic.'", "Device": "'.$DeviceName.'", "value": "'.$result.'"}');
          $Data = json_encode($JSON);
          $this->SendDataToParent($Data);
        break;
        case $CurrentSlatState:
          $result = intval($data);
          $Characteristic ="CurrentSlatState";      
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
      $form .= '{ "type": "SelectInstance", "name": "SlatID'.$count.'", "caption": "Gerät" },';
      $form .= '{ "type": "SelectVariable", "name": "SlatType'.$count.'", "caption": "SlatType " },';
      $form .= '{ "type": "SelectVariable", "name": "CurrentSlatState'.$count.'", "caption": "CurrentSlatState " },';
      $form .= '{ "type": "SelectVariable", "name": "SwingMode'.$count.'", "caption": "SwingMode" },';
      $form .= '{ "type": "Label", "label": "Soll eine eigene Variable geschaltet werden?" },';
      $form .= '{ "type": "CheckBox", "name": "SlatDummyOptional'.$count.'", "caption": "Ja" },';
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
    //Prüfen ob die ankommenden Daten für den Slat sind wenn ja, Status abfragen oder setzen
    if ($HomebridgeData->Action == "get" && $HomebridgeData->Service == "Slat") {
      $this->getState($HomebridgeData->Device, $HomebridgeData->Characteristic);
    }
    if ($HomebridgeData->Action == "set" && $HomebridgeData->Service == "Slat") {
      $this->setState($HomebridgeData->Device, $HomebridgeData->Value, $HomebridgeData->Characteristic);
    }
  }

  public function getState($DeviceName, $Characteristic) {
    $anzahl = $this->ReadPropertyInteger("Anzahl");
//$this->SendDebug('Dummy ',$anzahl, 0);
    for($count = 1; $count -1 < $anzahl; $count++) {

      //Hochzählen der Konfirgurationsform Variablen
      $DeviceNameCount = "DeviceName{$count}";
      $SlatTypeCount = "SlatType{$count}";
      $CurrentSlatStateCount = "CurrentSlatState{$count}";
      $SwingModeCount = "SwingMode{$count}";
      //Prüfen ob der übergebene Name aus dem Hook zu einem Namen aus der Konfirgurationsform passt
      $name = $this->ReadPropertyString($DeviceNameCount);
//$this->SendDebug('Dummy ',$name, 0);
      if ($DeviceName == $name) {
  //IPS Variable abfragen
         switch ($Characteristic) {
          case 'SwingMode':
            //abfragen
            $VariableID = $this->ReadPropertyInteger($SwingModeCount);
            $result = GetValue($VariableID);
            //$result = ($result) ? 'true' : 'false';
            break;
          case 'CurrentSlatState':
            // abfragen
            $VariableID = $this->ReadPropertyInteger($CurrentSlatStateCount);
            $result = GetValue($VariableID);
            break;
          case 'SlatType':
            // abfragen
            $VariableID = $this->ReadPropertyInteger($SlatTypeCount);
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
      $SlatTypeCount = "SlatType{$count}";
      $CurrentSlatStateCount = "CurrentSlatState{$count}";
      $SwingModeCount = "SwingMode{$count}";
      $DummyOptional = "SlatDummyOptional{$count}";
      //Prüfen ob der übergebene Name aus dem Hook zu einem Namen aus der Konfirgurationsform passt
      $name = $this->ReadPropertyString($DeviceNameCount);
      if ($DeviceName == $name) {
        $DummyOptionalValue = $this->ReadPropertyBoolean($DummyOptional);

        switch ($Characteristic) {
          case 'SwingMode':
            //Lightbulb State abfragen
            $VariableID = $this->ReadPropertyInteger($SwingModeCount);
            $variable = IPS_GetVariable($VariableID);
            $variableObject = IPS_GetObject($VariableID);
            if ($DummyOptionalValue == true) {
//              $this->SendDebug('setState Dummy SwingMode',$VariableID, 0);
              SetValue($VariableID, $result);
            } else {
              IPS_RequestAction($variableObject["ParentID"], $variableObject['ObjectIdent'], $result);
            }
            break;
          case 'CurrentSlatState':
            //Lightbulb Brightness abfragen
            $VariableID = $this->ReadPropertyInteger($CurrentSlatStateCount);
            $variable = IPS_GetVariable($VariableID);
            $variableObject = IPS_GetObject($VariableID);
            //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
            $result = $this->ConvertVariable($variable, $value);
            //Geräte Variable setzen
            if ($DummyOptionalValue == true) {
//              $this->SendDebug('setState Dummy SwingMode',$VariableID, 0);
              SetValue($VariableID, $result);
            } else {
              IPS_RequestAction($variableObject["ParentID"], $variableObject['ObjectIdent'], $result);
            }
            break;
          case 'SlatType':
            //Lightbulb Brightness abfragen
            $VariableID = $this->ReadPropertyInteger($SlatTypeCount);
            $variable = IPS_GetVariable($VariableID);
            $variableObject = IPS_GetObject($VariableID);
            //den übgergebenen Wert in den VariablenTyp für das IPS-Gerät umwandeln
            $result = $this->ConvertVariable($variable, $value);
            //Geräte Variable setzen
            if ($DummyOptionalValue == true) {
 //             $this->SendDebug('setState Dummy SwingMode',$VariableID, 0);
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
    $payload["service"] = "Slat";

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
