<?php
// this system DB
$config = array(
  'server'  => 'mongodb://202.85.213.54:27017',
  'options' => array('username' => 'admin',
                     'password' => 'iamzijiyou',
                     'connect'  => true),
);

$con = new Mongo($config['server'], $config['options']);

$tripfm = $con->tripfm;

$query = array('category' => 'wikipedia', 'regionId' => array('$exists' => false));

$iterator = $tripfm->POI->find($query);

$count = $iterator->count();

$wikipedia = array_values(iterator_to_array($iterator));

foreach ($wikipedia as $i => $wiki) {
  if (empty($wiki['center'])) continue;
  $geo = mongo_geo_query($tripfm, 'Region', $wiki['center'], 0, array(), 1);
  if (empty($geo)) continue;
  $region_id = $geo[0]['obj']['_id'];
  $tripfm->POI->update(array('_id' => $wiki['_id']), 
                       array('$set' => array('regionId' => $region_id)));
  printf("\r(%d/%d) %s%s\r", $i+1, $count, $wiki['name'], str_repeat(' ', 30));
}
echo PHP_EOL;

/**
 * 经纬度距离转换为国标距离
 * @param floag $radian
 */
function radian2dis($radian){
	$earth_radius = 6378.137; // km
	$pi = 3.1415926; // 元周率
	$ratio = ( (2 * $pi) / 360 ) * $earth_radius;
	$real_dis = $radian * $ratio;
	return floatval(round($real_dis * 10000) / 10);
}

/**
 * 格式化距离
 * @param string $dis
 * @return string $output
 */
function dis_format($dis){
  if($dis > 1){
    $output =  intval($dis * 10) / 10;
    //$output .= 'km';
  }else{
    $output = intval($dis * 10) * 100;
    //$output .= 'm';
  }
  return $output;
}

/**
 * 国标距离换为经纬度距离转
 * @param float $real_dis
 */
function dis2radian($real_dis){
  $earth_radius = 6378.137;//km
  $pi = 3.1415926;//元周率
  $ratio = ( (2 * $pi) / 360 ) * $earth_radius;
  return $real_dis / $ratio;
}

/**
 * mongo geo query
 * @param MongoDB $db
 * @param string  $collection
 * @param array   $center
 * @param float   $radius = 0
 * @param array   $query  = array
 * @param int     $limit  = 0
 * @return array
 */
function mongo_geo_query(MongoDB $db, $collection, array $center, $radius = 0, 
                         array $query = array(), $limit = 0) {
  $command = array(
    'geoNear'     => $collection,
    'near'        => $center, 
    'query'       => $query,
    'includeLocs' => true, 
  );       
  floatval($radius) > 0 && $command['maxDistance'] = floatval($radius);
  intval($limit) > 0    && $command['num']         = intval($limit);
  $res = $db->command($command);
  return @$res['results'] ?: array();
} 