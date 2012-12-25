<?php
//
//  Usage php calculate.php region_name
//

if ($argc != 2 || empty($argv[1])) {
  die("Usage. php {$argv[0]} region_name" . PHP_EOL);
}

$region_name = trim($argv[1]);

// this system DB
$config = array(
  'server'  => 'mongodb://202.85.213.54:27017',
  'options' => array('username' => 'admin',
                     'password' => 'iamzijiyou',
                     'connect'  => true),
);

$con = new Mongo($config['server'], $config['options']);

$tripfm = $con->tripfm;

$region = $tripfm->Region->findOne(array('name' => $region_name));

if (empty($region)) {
  die($region_name . " 不存在" . PHP_EOL);
}

$regionId = $region['_id'];

$iterator = $tripfm->POI->find(array('regionId' => $regionId, 'category' => array('$ne' => 'subway')));

$poi_arr = array_values(iterator_to_array($iterator));

$poi_cnt = count($poi_arr);

echo "{$region_name}共有{$poi_cnt}个poi" . PHP_EOL;

$distance = 2;

foreach ($poi_arr AS $index => $poi) {
  $query = array('category' => 'subway');

  $nearby_subway_station = isset($poi['center']) ? mongo_geo_query($tripfm, 'POI', $poi['center'], dis2radian($distance), $query, 3) : array();

  printf("\r" . str_repeat(" ", 100));
  printf("\r(%s/%d) {$distance}km 内临近地铁站个数:%d POI.name={$poi['name']}", 
         str_pad($index, strlen($poi_cnt), '0', STR_PAD_LEFT),
         $poi_cnt, count($nearby_subway_station));

  //if (!empty($poi['subway'])) continue;
  if (empty($nearby_subway_station)) continue;

  $subway_field = array();
  
  foreach ($nearby_subway_station AS $station) {
    //if (count($subway_field) == 3) break;
    $subway_field[] = array('poiId'  => $station['obj']['_id'], 
                            'lineid' => array_keys($station['obj']['line']),
                            'name'   => $station['obj']['name'],
                            'dis'    => radian2dis($station['dis']));
  }
  //$tripfm->POI->update(array('_id' => $poi['_id']), 
  //                     array('$set' => array('subway' => $subway_field)));
}
echo PHP_EOL;

/**
 * 经纬度距离转换为国标距离
 * @param floag $radian
 */
function radian2dis($radian){
	$earth_radius = 6378.137;//km
	$pi = 3.1415926;//元周率
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
  return $res['results'];
} 

