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
  return $res['value']['index'];
}

/**
 * 从wikipedia集合中找content信息
 * @param string $name
 * @return array()
 */
function get_wiki_center(MongoCollection $collection, $wiki_title) 
{
  $query = array('title' => $wiki_title);
  $wiki = $collection->findOne($query, array('center'));
  return !empty($wiki['center']) ? $wiki['center'] : false;
}

/**
 * init subway
 * @param string $name
 * @param array $region
 */
function get_init_subway(MongoDB $db, $region, $name, $tpl_refer)
{
  return array(
    'region'          => $region['englishName'],
    'regionId'        => $region['_id'],
    'system'          => '',
    'name'            => $name,
    'color'           => '',
    'lineid'          => get_mongo_increment($db, 'Subway', 'lineid'),
    'numberOfStation' => 0,
    'length'          => 0,
    'stationList'     => array(),
    'wiki'            => sprintf($tpl_refer, $name),
  );
}

/**
 * get poi
 * @param MongoCollection $wiki_collection
 * @param array $region
 * @param string $name
 * @param string $wiki_title
 */
function get_poi(MongoCollection $wiki_collection, $region, $name, $wiki_title)
{
  return array(
    'area'      => $region['englishName'],
    'name'      => $name,
    'regionId'  => $region['_id'],
    'category'  => 'subway',
    'center'    => get_wiki_center($wiki_collection, $wiki_title),
    'line'      => array(),
    'wikititle' => $wiki_title,
  );
}

/**
 * get station
 * @param MongoId $poiId
 * @param int $station_order
 */
function get_station(MongoId $poiId, $station_order)
{
  return array(
    'poiId'         => $poiId,
    'stationOrder'  => $station_order,
    'stationMinute' => 0,
    'transferLine'  => array(),
  );
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

/**
 * get help
 */
function get_help()
{
  global $argv;
  $usage = "Usage: php {$argv[0]} -c";
  $city_arr = array();
  $file_arr = scandir(dirname(dirname(__FILE__)) . '/city');
  foreach ($file_arr as $file) {
    if (is_dir($file)) continue;
    $city_arr[] = str_repeat(' ', strlen($usage) + 1) . basename($file, '.php');
  }
  if (empty($city_arr)) die("Cannot find any city config\n");
  die("$usage [city_name]\n" . implode("\n", $city_arr) . "\n");
}

/**
 * get http
 * @param string $url
 * @return string
 */
function get_http($url)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_AUTOREFERER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)');
  curl_setopt($ch, CURLOPT_HEADER, false);
  $content = curl_exec($ch);
  curl_close($ch); unset($ch);
  return $content;
}