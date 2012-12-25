<?php
include 'common.php';

$con = $_SGLOBAL['db']->con;

$tripfm = $con->tripfm;

$POI = $tripfm->POI;

$Subway = $tripfm->Subway;

$iterator = $POI->find(array('category' => 'subway'));

$station_arr = array_values(iterator_to_array($iterator));

foreach ($station_arr AS $station) {
  foreach ($station['line'] AS $lineId => $lineName) {
    $line = $Subway->findOne(array('lineid' => $lineId));
    foreach ($line['stationList'] AS $station_detail) {
      if (strval($station['_id']) != strval($station_detail['poiId'])) continue;
      $param = array(
        'name'  => $line['name'],
        'order' => intval($station_detail['stationOrder']),
      );
      $POI->update(array('_id'  => $station['_id']), 
                   array('$set' => array("line.$lineId" => $param)));
    }
  }
}