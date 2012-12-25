<?php
include 'function_tpl.php'; // include function tpl


/**
 * get mongo increment
 * @param string $collection
 * @param string field
 * @return int
 */
function g_mongo_inc($collection, $field) 
{
  $cmd = array(
    'findAndModify' => 'autoIncrement',
    'query'         => array('field' => $field, 'collection' => $collection),
    'update'        => array('$inc' => array('index' => 1)),
    'new'           => true,
  );
  $res = Mf::$mongo['tripfm']->command($cmd);
  return $res['value']['index'];
}

/**
 * 从wikipedia集合中找content信息
 * @param string $name
 * @return array()
 */
function g_get_wiki($name, $nid = array())
{
  $query = array('title' => $name);
  if (!empty($nid)) $query['_id'] = array('$nin' => $nid);
  // find
  $wiki = Mf::$mongo['tripfm.Wikipedia']->findOne($query);
  if (!empty($wiki) && !empty($wiki['content'])) {
    // 排除已经查过的
    $nid[] = $wiki['_id'];
    // 如果有跳转则递归
    if (preg_match('/#REDIRECT\s?(.+)/i', $wiki['content'], $match)) {
      $wiki = g_get_wiki($match[1], $nid);
    }
  }
  return $wiki;
}

/**
 * 经纬度距离转换为国标距离
 * @param floag $radian
 */
function g_radian2dis($radian){
  $earth_radius = 6378.137; // km
  $pi = 3.1415926; // 元周率
  $ratio = ( (2 * $pi) / 360 ) * $earth_radius;
  $real_dis = $radian * $ratio;
  return floatval(round($real_dis * 10000) / 10);
}

/**
 * 国标距离换为经纬度距离转
 * @param float $real_dis
 */
function g_dis2radian($real_dis){
  $earth_radius = 6378.137; // km
  $pi = 3.1415926; // 元周率
  $ratio = ( (2 * $pi) / 360 ) * $earth_radius;
  return $real_dis / $ratio;
}