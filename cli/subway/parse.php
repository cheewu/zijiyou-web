<?php
include 'common.php';

$filename = trim($argv[1], './ ');
$data = file_get_contents($filename);
$data = explode("\n", $data);
$data = array_filter($data);

list($area, $line_name) = explode('/', $filename);
$line_name = basename($line_name, '.txt');

$subway = array(
  'region' => fan2jian($area),
  'system' => '',
  'name' => fan2jian($line_name),
  'color' => '',
  'lineid' => get_mongo_increment('tripfm', 'Subway', 'lineid'),
  'numberOfStation' => 0,
  'length' => 0,
  'stationList' => array(),
  'wiki' => "http://zh.wikipedia.org/wiki/$line_name",
);

$station_tpl = array(
  'wikiName' => '',
  'stationName' => '',
  'stationOrder' => '',
  'stationMinute' => '',
  'transferLine' => array(),
);

$poi_tpl = array(
  'area' => fan2jian($area),
  'name' => '',
  'center' => '',
  'lineName' => fan2jian($line_name),
  'lineid' => $subway['lineid'],
  'category' => 'subway',
  'wikiName' => '',
);

//var_dump($data);exit;

foreach ($data AS $v) {
  $poi = $poi_tpl;
  $station = $station_tpl;
  if (!preg_match("#^\{\{BS(\d)#", $v, $matches)) continue;
  $piece_cnt = $matches[1] + 3;
  $pieces = explode("|", $v, $piece_cnt);
  //$param['origin'] = $v;
  $minute = floatval($pieces[$piece_cnt - 2]);
  if (!preg_match("#\[([^\[\]]+)\|([^\[\]]+)\]#", $pieces[$piece_cnt - 1], $matches)) continue;
  $wikiName = fan2jian($matches[1]);
  $center = get_wiki_center($wikiName);
  $stationName = trim(fan2jian($matches[2]), "'");
  $poi = array_merge($poi, array(
           'name' => $stationName,
           'center' => $center,
           'wikiName' => $wikiName,
           
         ));
  $station = array_merge($station, array(
               'wikiName' => $wikiName,
               'stationName' => $stationName,
               'stationMinute' => $minute,
             ));
  $id = $_SGLOBAL['db']->POI_insert($poi);
  $station['poiId'] = strval($id);
  $subway['stationList'][] = $station;
  
}   

$subway['numberOfStation'] = count($subway['stationList']);

usort($subway['stationList'], 'cmp');

foreach ($subway['stationList'] AS $index => $sub_station) {
  $subway['stationList'][$index]['stationOrder'] = $index;
}

$_SGLOBAL['db']->Subway_insert($subway);

function cmp($station1, $station2)
{
    if ($station1['stationMinute'] == $station2['stationMinute']) {
        return 0;
    }
    return ($station1['stationMinute'] < $station2['stationMinute']) ? -1 : 1;
}


 