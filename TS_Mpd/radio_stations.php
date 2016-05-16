<?
function get_available_stations(){
  //Taken URLs from  http://tunein.com/search/?query=WDR2 
  $RadioStations =  Array(
            Array( ('name') => "FFN",              ('url') => "tunein:station:s84672" ),
            Array( ('name') => "FFH",              ('url') => "tunein:station:s223993" ),
            Array( ('name') => "Radio Lippe",      ('url') => "tunein:station:s96523" ),
            Array( ('name') => "OE3",              ('url') => "tunein:station:s8007" ),
            Array( ('name') => "Antenne 1",        ('url') => "tunein:station:s96752" ),
            Array( ('name') => "Antenne Bayern",   ('url') => "tunein:station:s42824" ),
            Array( ('name') => "N-JOY",            ('url') => "tunein:station:s97008" ),
            Array( ('name') => "WDR2",             ('url') => "tunein:station:s100187" ),
            Array( ('name') => "1LIVE",            ('url') => "tunein:station:s104811" )
                         );

   // sort by name
  foreach ($RadioStations as $key => $row) {
      $dates[$key]  = $row['name']; 
  }

  array_multisort($dates, SORT_ASC, $RadioStations);

  return  $RadioStations ;
}

function get_station_url($name, $RadioStations = null){

  if ( $RadioStations === null ){ $RadioStations = get_available_stations(); };

  foreach ( $RadioStations as $key => $val ) {
      if ($val['name'] === $name) {
          return $RadioStations[$key]['url'];
      }
  }
   throw new Exception("Radio station " . $name . " is unknown" );
}

?>
