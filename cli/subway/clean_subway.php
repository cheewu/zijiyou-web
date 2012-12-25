<?php
/**
 * get mongo increment
 * @param string $dbname
 * @param string $collection
 * @param string field
 * @return int
 */
function get_mongo_increment(MongoDB $db, $collection, $field) 
{
  $cmd = array(
    'findAndModify' => 'autoIncrement',
    'query' => array('field' => $field, 'collection' => $collection),
    'update' => array('$inc' => array('index' => 1)),
    'new' => true,
  );
  $res = $db->command($cmd);
  if ($res['ok'] == 0) {
    $db->autoIncrement->insert(array(
      'collection' => $collection,
      'field' => $field,
      'index' => 1
    ));
    $res['value']['index'] = 1;
  }
  return $res['value']['index'];
}

/**
 * get con
 * @return Mongo $db
 */
function get_con() 
{
  static $con = null;
  $config = array(
    'server'  => 'mongodb://202.85.213.54:27017',
    'options' => array('username' => 'admin',
                       'password' => 'iamzijiyou',
                       'connect'  => true),
  );
  if (!($con instanceof Mongo)) {
    $con = new Mongo($config['server'], $config['options']);
  }
  return $con;
}

$con = get_con();
$query = array(
'name' => 'abcStationa',
'subway.line_id' => 11	
);
$update = array(
'subway.$.subline' => array('id' => 1213),
);
var_dump($con->Test->subway->update($query, array('$pull' => $update), array('safe' => true)));
exit;

$tripfm = $con->tripfm;



$iterator = $tripfm->Subway->find();

foreach (array_values(iterator_to_array($iterator)) AS $subway) {
  $lineOld = $subway['stationList'];
  unset($subway['stationList'], $subway['lineid']);
  $subway['stationCount'] = $subway['numberOfStation'];
  unset($subway['numberOfStation']);
  $subway['lineId'] = get_mongo_increment($tripfm, 'Subway', 'lineId');
  $subline = array(
    'id' => get_mongo_increment($tripfm, 'Subway', 'sublineId'),
    'name' => $subway['name'] . '-1'
  );
  foreach ($lineOld AS $station) {
    $station_all[strval($station['poiId'])][$subway['lineId']][] = $subline['id']; 
    $subline['list'][] = array(
      'poiId'  => $station['poiId'],
      'order'  => $station['stationOrder'] + 1,
      'minute' => intval($station['stationMinute']),
    );
  }
  $subway['subline'][] = $subline;
  $tripfm->Subway->update(array('_id' => $subway['_id']), $subway);
}
foreach ($station_all AS $pid => $line) {
  $subway = array();
  foreach ($line AS $line_id => $subline) {
    $sub = array();
    $sub['lineId'] = $line_id;
    foreach ($subline AS $subline_id) {
      $sub['subline'][] = array('id' => $subline_id);
    }
    $subway[] = $sub;
  }
  $poi = $tripfm->POI->findOne(array('_id' => new MongoId($pid)));
  unset($poi['line']);
  $poi['subway'] = $subway;
  $tripfm->POI->update(array('_id' => $poi['_id']), $poi);
}
//var_dump($station_all);

