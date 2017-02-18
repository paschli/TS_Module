<?
require_once(__DIR__ . "/../HomeKitService.php");

class IPS_HomebridgeLightSensor extends HomeKitService {
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
        $this->RegisterPropertyString($DeviceName, "");
        $this->RegisterPropertyInteger($LightSensorID, 0);
        $this->RegisterPropertyInteger($CurrentAmbientLightLevel, 0);
      }
  }
  public function ApplyChanges() {
      //Never delete this line!
      parent::ApplyChanges();
      //Setze Filter für ReceiveData
      $this->SetReceiveDataFilter(".*LightSensor.*");
      $anzahl = $this->ReadPropertyInteger("Anzahl");
      $Devices = [];
      for($count = 1; $count-1 < $anzahl; $count++) {
        $Devices[$count]["DeviceName"] = $this->ReadPropertyString("DeviceName{$count}");
        $Devices[$count]["CurrentAmbientLightLevel"] = $this->ReadPropertyInteger("CurrentAmbientLightLevel{$count}");


        $BufferName = $Devices[$count]["DeviceName"]." Temperatur";

        //Alte Registrierungen auf Variablen Veränderung aufheben
        $UnregisterBufferIDs = [];
        array_push($UnregisterBufferIDs,$this->GetBuffer($BufferName));
        $this->UnregisterMessages($UnregisterBufferIDs, 10603);

        if ($Devices[$count]["DeviceName"] != "") {
          //Regestriere State Variable auf Veränderungen
          $RegisterBufferIDs = [];
          array_push($RegisterBufferIDs,$Devices[$count]["CurrentAmbientLightLevel"]);
          $this->RegisterMessages($RegisterBufferIDs, 10603);
          //Buffer mit den aktuellen Variablen IDs befüllen für State und Brightness
          $this->SetBuffer($BufferName,$Devices[$count]["CurrentAmbientLightLevel"]);
          //Accessory anlegen
          $this->addAccessory($Devices[$count]["DeviceName"]);
        }
        else {
          return;
        }
      }
      $DevicesConfig = serialize($Devices);
      $this->SetBuffer("LightSensor Config",$DevicesConfig);
    }
  public function Destroy() {
  }

  public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
    $Devices = unserialize($this->getBuffer("LightSensor Config"));
    if ($Data[1] == true) {
      $anzahl = $this->ReadPropertyInteger("Anzahl");

      for($count = 1; $count-1 < $anzahl; $count++) {
        $Device = $Devices[$count];

        //Prüfen ob die SenderID gleich der Temperatur Variable ist, dann den aktuellen Wert an die Bridge senden
        if ($Device["CurrentAmbientLightLevel"] == $SenderID) {
          $DeviceName = $Device["DeviceName"];
          $Characteristic = "CurrentAmbientLightLevel";
          $data = $Data[0];
          $result = number_format($data, 2, '.', '');
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
      $form .= '{ "type": "SelectInstance", "name": "LightSensorID'.$count.'", "caption": "Gerät" },';
      $form .= '{ "type": "SelectVariable", "name": "CurrentAmbientLightLevel'.$count.'", "caption": "CurrentAmbientLightLevel" },';
      $form .= '{ "type": "Button", "label": "Löschen", "onClick": "echo HBLightSensor_removeAccessory('.$this->InstanceID.','.$count.');" },';
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
    $Devices = unserialize($this->getBuffer("TemperaturSensor Config"));
    $anzahl = $this->ReadPropertyInteger("Anzahl");

    for($count = 1; $count -1 < $anzahl; $count++) {
      $Device = $Devices[$count];
      $name = $Device["DeviceName"];
      //Prüfen ob der übergebene Name zu einem Namen aus der Konfirgurationsform passt wenn ja Wert an die Bridge senden
      if ($DeviceName == $name) {
        //IPS Variable abfragen
        $CurrentAmbientLightLevelID = $Device["CurrentAmbientLightLevel"];
        $result = GetValue($CurrentAmbientLightLevelID);
        $result = number_format($result, 2, '.', '');
        $this->sendJSONToParent("callback", $Characteristic, $DeviceName, $result);
        return;
      }
    }
  }
  private function addAccessory($DeviceName) {
    //Payload bauen
    $payload["name"] = $DeviceName;
    $payload["service"] = "LightSensor";
    $payload["CurrentAmbientLightLevel"] = $CurrentAmbientLightLevel;

    $array["topic"] ="add";
    $array["payload"] = $payload;
    $data = json_encode($array);
    $SendData = json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Buffer" => $data));
    @$this->SendDataToParent($SendData);
  }
}
?>
