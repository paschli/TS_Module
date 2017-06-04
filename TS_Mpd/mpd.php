<?php

//Sonos PHP Script
//Copyright: Michael Maroszek
//Version: 1.0, 09.07.2009
// für MPD umgebaut TS 2015

class PHPmpd {
	private $address = "";

	public function __construct( $address ) {
	   $this->address = $address;
	}


	public function SetRadio($radio)
	{
$header='POST /ctl/AVTransport HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#SetAVTransportURI"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':49152';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
   <s:Body>
      <u:SetAVTransportURI xmlns:u="urn:schemas-upnp-org:service:AVTransport:1">
         <InstanceID>0</InstanceID>
         <CurrentURI>'.$radio.'</CurrentURI>
         <CurrentURIMetaData />
      </u:SetAVTransportURI>
   </s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;

		$this->sendPacket($content);
	}

	public function GetMute()
	{

$header='POST /ctl/RenderingControl HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#GetMute"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':49152';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
   <s:Body>
      <u:GetMute xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1">
         <InstanceID>0</InstanceID>
         <Channel>Master</Channel>
      </u:GetMute>
   </s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;

//		return (bool)$this->sendPacket($content);
    $ret = $this->XMLsendPacket($content);
		$xmlParser = xml_parser_create("UTF-8");
 		$ret=preg_replace("#(.*)<CurrentMute>(.*?)\</CurrentMute>(.*)#is",'$2',$ret);
		return (bool)$ret;
    
	}

	public function GetVolume()
	{

$header='POST /ctl/RenderingControl HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#GetVolume"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':49152';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
   <s:Body>
      <u:GetVolume xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1">
         <InstanceID>0</InstanceID>
         <Channel>Master</Channel>
      </u:GetVolume>
   </s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;

//		return (int)$this->sendPacket($content);
    $ret = $this->XMLsendPacket($content);

		$xmlParser = xml_parser_create("UTF-8");
 		$ret=preg_replace("#(.*)<CurrentVolume>(.*?)\</CurrentVolume>(.*)#is",'$2',$ret);
		return (int)$ret;

	} 


	public function SetVolume($volume)
	{

$laenge=321 + (strlen($volume));
$content='POST /ctl/RenderingControl HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':49152
CONTENT-LENGTH: '.$laenge.'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#SetVolume"

<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><u:SetVolume xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><Channel>Master</Channel><DesiredVolume>'.$volume.'</DesiredVolume></u:SetVolume></s:Body></s:Envelope>';
		$this->sendPacket($content);
	}

	public function Stop()
	{
$header='POST /ctl/AVTransport HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Stop"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':49152';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
   <s:Body>
      <u:Stop xmlns:u="urn:schemas-upnp-org:service:AVTransport:1">
         <InstanceID>0</InstanceID>
      </u:Stop>
   </s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;
		$this->sendPacket($content);
	}

	public function Next()
	{
$header='POST /ctl/AVTransport HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Next"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':49152';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
   <s:Body>
      <u:Next xmlns:u="urn:schemas-upnp-org:service:AVTransport:1">
         <InstanceID>0</InstanceID>
      </u:Next>
   </s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;
		$this->sendPacket($content);
	}
	public function Previous()
	{
$header='POST /ctl/AVTransport HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Previous"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':49152';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
   <s:Body>
      <u:Previous xmlns:u="urn:schemas-upnp-org:service:AVTransport:1">
         <InstanceID>0</InstanceID>
      </u:Previous>
   </s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;
		$this->sendPacket($content);
	}

	public function Pause()
	{

$content='POST /ctl/AVTransport HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':49152
CONTENT-LENGTH: 252
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Pause"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Pause xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:Pause></s:Body></s:Envelope>';

		$this->sendPacket($content);
	}

	public function Play()
	{

$content='POST /ctl/AVTransport HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':49152
CONTENT-LENGTH: 266
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Play"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Play xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><Speed>1</Speed></u:Play></s:Body></s:Envelope>';

		$this->sendPacket($content);
	}

	public function GetTransportInfo()
	{
$header='POST /ctl/AVTransport HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#GetTransportInfo"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':49152';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
   <s:Body>
      <u:GetTransportInfo xmlns:u="urn:schemas-upnp-org:service:AVTransport:1">
         <InstanceID>0</InstanceID>
      </u:GetTransportInfo>
   </s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;
		$returnContent = $this->sendPacket($content);
    $ret = $this->XMLsendPacket($content);
		$xmlParser = xml_parser_create("UTF-8");
 		$ret=preg_replace("#(.*)<CurrentTransportState>(.*?)\</CurrentTransportState>(.*)#is",'$2',$ret);

		if ($ret === "PLAYING") {
		   return 1;
		} elseif ($ret === "PAUSED_PLAYBACK") {
		   return 2;
		} elseif ($ret === "STOPPED") {
		   return 3;
		} 
  }


	public function GetPositionInfo()
	{
$header='POST /ctl/AVTransport HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#GetPositionInfo"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':49152';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
   <s:Body>
      <u:GetPositionInfo xmlns:u="urn:schemas-upnp-org:service:AVTransport:1">
         <InstanceID>0</InstanceID>
      </u:GetPositionInfo>
   </s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;

    $ret = $this->XMLsendPacket($content);
 		$xmlParser = xml_parser_create("UTF-8");
 		$positionInfo["title"]=preg_replace("#(.*)dc:title&gt;(.*?)\&lt;/dc:title&gt(.*)#is",'$2',$ret);
 		$positionInfo["creator"]=preg_replace("#(.*)dc:creator&gt;(.*?)\&lt;/dc:creator&gt(.*)#is",'$2',$ret);

    return $positionInfo;
	}

	public function GetMediaInfo()
	{
$header='POST /ctl/AVTransport HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#GetMediaInfo"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':49152';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
   <s:Body>
      <u:GetMediaInfo xmlns:u="urn:schemas-upnp-org:service:AVTransport:1">
         <InstanceID>0</InstanceID>
      </u:GetMediaInfo>
   </s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;
		$ret = $this->XMLsendPacket($content);
		$xmlParser = xml_parser_create("UTF-8");
 		$mediaInfo["CurrentURI"]=preg_replace("#(.*)<CurrentURI>(.*?)\</CurrentURI>(.*)#is",'$2',$ret);
 	//	$ret=preg_replace("#(.*)<CurrentURIMetaData>(.*?)\</CurrentURIMetaData>(.*)#is",'$2',$ret);
 		$mediaInfo["title"]=preg_replace("#(.*)dc:title&gt;(.*?)\&lt;/dc:title&gt(.*)#is",'$2',$ret);
 		$mediaInfo["creator"]=preg_replace("#(.*)dc:creator&gt;(.*?)\&lt;/dc:creator&gt(.*)#is",'$2',$ret);

		return $mediaInfo;
//    echo $mediaInfo[1];
	}
	
	public function SetMute($mute)
	{

		if($mute) { $mute = "1"; } else { $mute = "0"; }

$content='POST /ctl/RenderingControl HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':49152
CONTENT-LENGTH: 314
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#SetMute"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:SetMute xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><Channel>Master</Channel><DesiredMute>'.$mute.'</DesiredMute></u:SetMute></s:Body></s:Envelope>';

		$this->sendPacket($content);
	}
	
	
	
/***************************************************************************
				Helper / sendPacket
***************************************************************************/

/**
 * XMLsendPacket
 *
 * - <b>NOTE:</b> This function does send of a soap query and DOES NOT filter a xml answer
 * - <b>Returns:</b> Answer as XML
 *
 * @return Array
 */
	private function XMLsendPacket( $content )
	{
		$fp = fsockopen($this->address, 49152 /* Port */, $errno, $errstr, 10);
		if (!$fp)
		    throw new Exception("Error opening socket: ".$errstr." (".$errno.")");
		    
		fputs ($fp, $content);
		$ret = "";
		$buffer = "";
		while (!feof($fp)) {
			$buffer = fgets($fp,128);
		//	echo "\n;" . $buffer . ";\n"; //DEBUG
			$ret.= $buffer;
		}

		// echo "\n\nReturn:" . $ret . "!!\n";
		fclose($fp);

		if(strpos($ret, "200 OK") === false)
			throw new Exception("Error sending command: ".$ret);
//		$array = preg_split("/\n/", $ret); 		
//		return $array[count($array) - 1];
    return $ret;
	}

	private function sendPacket( $content )
	{
		$fp = fsockopen($this->address, 49152 /* Port */, $errno, $errstr, 10);
		if (!$fp)
		    throw new Exception("Error opening socket: ".$errstr." (".$errno.")");

		fputs ($fp, $content);
		$ret = "";
		while (!feof($fp)) {
			$ret.= fgetss($fp,128); // filters xml answer
		}
    //    echo($ret); 

		fclose($fp);

		if(strpos($ret, "200 OK") === false)
   	throw new Exception("Error sending command: ".$ret);
// TAG_DEBUG_DEEP sendpacketdebug
//		 echo "sendPacketDebug: "; //DEBUG
//		  print_r($ret);
		
		$array = preg_split("/\n/", $ret);

		return $array[count($array) - 1];
	}

} 
?>