<?
class TS_HomebridgeTemperatur extends IPSModule {
  public function Create() {
      //Never delete this line!
      parent::Create();
      $this->ConnectParent("{86C2DE8C-FB21-44B3-937A-9B09BB66FB76}");   //IPS-HomebridgeSplitter
        $this->RegisterPropertyInteger("Anzahl",1);

      for($count = 1; $count -1 < 99; $count++) {
        $DeviceName = "DeviceName{$count}";
        $TempDeviceID = "TempDeviceID{$count}";
        $VariableTemp = "VariableTemp{$count}";
        $this->RegisterPropertyString($DeviceName, "");
        $this->RegisterPropertyInteger($TempDeviceID, 0);
        $this->RegisterPropertyInteger($VariableTemp, 0);
      }
  }
  public function ApplyChanges() {
      //Never delete this line!
      parent::ApplyChanges();
      $anzahl = $this->ReadPropertyInteger("Anzahl");
      for($count = 1; $count-1 < $anzahl; $count++) {
        $DeviceNameID = "DeviceName{$count}";

/////////////////////////////////////////////////
$alarmskript= '<? 

$value = ($_IPS["VALUE"]); //Wert vom Ereigniss holen...
$value = str_replace(\',\', \'.\', $value);  
$data =\'{"topic": "setValue", "payload": {"name": \'.$DeviceName.\', "characteristic": "CurrentTemperature", "value": \'.$value.\'}}\'; 
WSC_SendText(39016, $data)
?>';
  $alarmskript_ID = $this->RegisterScript($DeviceNameID, $DeviceNameID, $alarmskript);
  IPS_SetHidden($alarmskript_ID,true);
//  $this->Registerevent2($alarmskript_ID,$steuer_id); 

  $sk_id=$this->ReadPropertyString("VariableTemp{$count}");
  if ( IPS_ScriptExists($sk_id)){
      IPS_SetScriptContent ( $sk_id, $alarmskript);
  }

/////////////////////////////////////////////////



        if ($this->ReadPropertyString($DeviceNameID) != "") {
          $this->addAccessory($this->ReadPropertyString($DeviceNameID));
        }
        else {
          return;
        }
      }
    }

  public function Destroy() {
  }

  public function GetConfigurationForm() {
    $anzahl = $this->ReadPropertyInteger("Anzahl");
    $form = '{"elements":
              [
                { "type": "NumberSpinner", "name": "Anzahl", "caption": "Anzahl" },';
    // Zählen wieviele Felder in der Form angelegt werden müssen
    for($count = 1; $count-1 < $anzahl; $count++) {
      $form .= '{ "type": "ValidationTextBox", "name": "DeviceName'.$count.'", "caption": "HB Gerätename" },';
      $form .= '{ "type": "SelectInstance", "name": "TempDeviceID'.$count.'", "caption": "Gerät" },';
      $form .= '{ "type": "SelectVariable", "name": "VariableTemp'.$count.'", "caption": "Temperatur" },';
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
    IPS_LogMessage("Temperatur ReceiveData", $JSONString);
      if ($HomebridgeData->Action == "get" && $HomebridgeData->Service == "CurrentTemperature") {
        $this->getVar($HomebridgeData->Device, $HomebridgeData->Characteristic);
      }
  }

  public function getVar($DeviceName, $Characteristic) {
    for($count = 1; $count -1 < $this->ReadPropertyInteger("Anzahl"); $count++) {
      //Hochzählen der Konfirgurationsform Variablen
      $TempDeviceID = "TempDeviceID{$count}";
      $VariableTemp = "VariableTemp{$count}";
          IPS_LogMessage("Temperatur getVar", $DeviceName);
      //Prüfen ob der übergebene Name aus dem Hook zu einem Namen aus der Konfirgurationsform passt
      if ($DeviceName == $this->ReadPropertyString("DeviceName{$count}")) {
        //IPS Variable abfragen
        $result = GetValue($this->ReadPropertyInteger($VariableTemp));
        $result = number_format($result, 2, '.', '');
        $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
        $JSON['Buffer'] = utf8_encode('{"topic": "callback", "Characteristic": "'.$Characteristic.'", "Device": "'.$DeviceName.'", "value": "'.$result.'"}');
        $Data = json_encode($JSON);
        $this->SendDataToParent($Data);
        return;
      }
    }
  }

  private function addAccessory($DeviceName) {
    $JSON['DataID'] = "{018EF6B5-AB94-40C6-AA53-46943E824ACF}";
    $JSON['Buffer'] = utf8_encode('{"topic": "add", "name": "'.$DeviceName.'", "service": "TemperatureSensor"}');
    $Data = json_encode($JSON);
    $this->SendDataToParent($Data);
  }
}
?>
