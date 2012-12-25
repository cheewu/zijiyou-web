<?php
include 'common.php';

$con = $_SGLOBAL['db']->con;

$tripfm = $con->tripfm;

$subway_it = $tripfm->Subway->find();

$subway_arr = array_values(iterator_to_array($subway_it));

$station_arr = array();

foreach ($subway_arr AS $k => $line) {
  
  $region_name = $line['region'];
  
  $region = $tripfm->Region->findOne(array('name' => $line['region']), array('_id'));
  
  $region_id = $region['_id'];
  
  $subway_arr[$k]['regionId'] = $region_id;
  
  foreach ($line['stationList'] AS $station) {
    
    if (isset($station_arr[$station['stationName']])) {
      $station_arr[$station['stationName']]['line'][$line['lineid']] = array(
        'name'  => $line['name'],
        'order' => $station['stationOrder'],
      );
      continue;
    }
    $wikiName = $station['wikiName'];
    $station_arr[$station['stationName']] = array(
      'area'      => $region_name,
      'name'      => $station['stationName'],
      'wikititle' => $wikiName,
      'regionId'  => $region_id,
      'line'      => array($line['lineid'] => array(
                       'name'  => $line['name'],
                       'order' => $station['stationOrder'])),
      'center'    => get_wiki_center($wikiName),
      'category'  => 'subway',
    ); 
  }
}

foreach ($station_arr AS $index => $poi) {
  $tripfm->POI->insert($poi);
  $station_arr[$index]['_id'] = $poi['_id'];
  var_dump("insert {$poi['name']}");
}

foreach ($subway_arr AS $k => $line) {
  foreach ($line['stationList'] AS $index => $station) {
    $poi = $station_arr[$station['stationName']];
    $line['stationList'][$index] = array(
      'poiId'         => $poi['_id'],
      'stationOrder'  => $station['stationOrder'],
      'stationMinute' => $station['stationMinute'],
      'transferLine'  => $station['transferLine'],
    );
  }
  $tripfm->Subway->update(array('_id' => $line['_id']), 
    array('$set' => array(
                      'regionId'    => $line['regionId'],
                      'stationList' => $line['stationList']),
                    )
    );
  var_dump("update {$line['name']}");
}

var_dump("ok");

