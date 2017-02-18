<?
require_once(__DIR__ . "/../HomeKitService.php");
class IPS_HomebridgeContact extends HomeKitService {
  public function Create() {
      //Never delete this line!
      parent::Create();
      //Anzahl die in der Konfirgurationsform angezeigt wird - Hier Standard auf 1
      $this->RegisterPropertyInteger("Anzahl",1);
      $this->ConnectParent("{86C2DE8C-FB21-44B3-937A-9B09BB66FB76}");
      //99 Geräte können pro Konfirgurationsform angelegt werden
      for($count = 1; $count -1 < 99; $count++) {
        $DeviceName = "DeviceName{$count}";
        $ContaktID = "ContaktID{$count}";
        $ContactState = "ContactState{$count}";
        $ContactInverse = "ContactInverse{$count}";
        $this->RegisterPropertyString($DeviceName, "");
        $this->RegisterPropertyInteger($ContaktID, 0);
        $this->RegisterPropertyInteger($ContactState, 0);
        $this->RegisterPropertyBoolean($ContactInverse, false);
      }
  }
  public function ApplyChanges() {
      //Never delete this line!
      parent::ApplyChanges();
      //Setze Filter für ReceiveData
      $this->SetReceiveDataFilter(".*ContactSensor.*");
      $anzahl = $this->ReadPropertyInteger("Anzahl");
      $Devices = [];
      for($count = 1; $count-1 < $anzahl; $count++) {
        $Devices[$count]["DeviceName"] = $this->ReadPropertyString("DeviceName{$count}");
        $Devices[$count]["ContactState"] = $this->ReadPropertyInteger("ContactState{$count}");
        $BufferName = $Devices[$count]["DeviceName"]." ContactState";
        //Alte Registrierungen auf Variablen Veränderung aufheben
        $UnregisterBufferIDs = [];
        array_push($UnregisterBufferIDs,$this->GetBuffer($BufferName));
        $this->UnregisterMessages($UnregisterBufferIDs, 10603);
        if ($Devices[$count]["DeviceName"] != "") {
          //Regestriere ContactState Variable auf Veränderungen
          $RegisterBufferIDs = [];
          array_push($RegisterBufferIDs,$Devices[$count]["ContactState"]);
          $this->RegisterMessages($RegisterBufferIDs, 10603);
          //Buffer mit den aktuellen Variablen IDs befüllen für ContactState und ContactInverse
          $this->SetBuffer($BufferName,$Devices[$count]["ContactState"]);
          $this->addAccessory($Devices[$count]["DeviceName"]);
        } else {
          return;
        }
      }
      $DevicesConfig = serialize($Devices);
      $this->SetBuffer("Contact Config",$DevicesConfig);
    }
  public function Destroy() {
  }
  public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
    $Devices = unserialize($this->getBuffer("Contact Config"));
    if ($Data[1] == true) {
      $anzahl = $this->ReadPropertyInteger("Anzahl");
      for($count = 1; $count-1 < $anzahl; $count++) {
        $Device = $Devices[$count];
        $DeviceName = $Device["DeviceName"];
        $ContactInverseCount= "ContactInverse{$count}";
        //Prüfen ob die SenderID gleich der ContactState oder ContactInverse Variable ist, dann den aktuellen Wert an die Bridge senden
        if ($SenderID == $Device["ContactState"]) {
          $ContactInverse = $this->ReadPropertyBoolean($ContactInverseCount);
          $Characteristic = "ContactState";
          $data = $Data[0];
          $result = intval($data);
          if ($ContactInverse == true) {
           $result = ($data) ? '0' : '1';
          }
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
      $form .= '{ "type": "SelectInstance", "name": "ContaktID'.$count.'", "caption": "Gerät" },';
      $form .= '{ "type": "SelectVariable", "name": "ContactState'.$count.'", "caption": "ContactState" },';
      $form .= '{ "type": "Label", "label": "Contact invertieren ?" },';
      $form .= '{ "type": "CheckBox", "name": "ContactInverse'.$count.'", "caption": "Ja" },';
      $form .= '{ "type": "Button", "label": "Löschen", "onClick": "echo HBContact_removeAccessory('.$this->InstanceID.','.$count.');" },';
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
    $Devices = unserialize($this->getBuffer("ContactInverseSensor Config"));
    $anzahl = $this->ReadPropertyInteger("Anzahl");
    for($count = 1; $count -1 < $anzahl; $count++) {
      $ContactInverseCount = "ContactInverse{$count}"; // ContactInverse
      $Device = $Devices[$count];
      //Prüfen ob der übergebene Name aus dem Socket zu einem Namen aus der Konfirgurationsform passt
      $name = $Device["DeviceName"];
      if ($DeviceName == $name) {
        //IPS Variable abfragen
        switch ($Characteristic) {
          case 'ContactState':
            $ContactInverse = $this->ReadPropertyBoolean($ContactInverseCount);
            //ContactInverseSensor ContactState abfragen
            $result = intval(GetValue($Device["ContactState"]));
            if ($ContactInverse == true) {
             $result = ($result) ? '0' : '1';
            }
            break;
        }
        //Status an die Bridge senden
        $this->sendJSONToParent("callback", $Characteristic, $DeviceName, $result);
        return;
      }
    }
  }

  private function addAccessory($DeviceName) {
    //Payload bauen
    $payload["name"] = $DeviceName;
    $payload["service"] = "ContactSensor";
   
    $array["topic"] ="add";
    $array["payload"] = $payload;
    $data = json_encode($array);
    $SendData = json_encode(Array("DataID" => "{018EF6B5-AB94-40C6-AA53-46943E824ACF}", "Buffer" => $data));
    @$this->SendDataToParent($SendData);
  }
}
?>
