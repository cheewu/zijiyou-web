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

$query = array(
  //'regionId' => array('$ne' => new MongoId('4e8c0922d0c2ff4823000417')),
  //'category' => array('$ne' => 'attraction')
);

$col = $tripfm->Region;

$wikipedia = $tripfm->Wikipedia;

$fields = array('desc', 'wikiContent', 'name', 'wikititle', 'regionId');
$fields = array('name', 'wikititle', 'regionId');

$total = $col->find($query, $fields)->count();

$ps = 1000;

$total_pgs = ceil($total / $ps);

$find_cnt = 0;

for ($i = 0; $i < $total_pgs; $i++) {
  $it = $col->find($query, $fields)->skip($i * $ps)->limit($ps);
  foreach (iterator_to_array($it) as $pid => $value) {
    //if (@$value['category'] == 'attraction' && 
    //     $value['regionId']->{'$id'} == '4e8c0922d0c2ff4823000417') continue;
    $wikititle = isset($value['wikititle']) ? trim($value['wikititle']) : "";
    if (empty($wikititle)) $wikititle = $value['name'];
    $wiki = get_wiki($wikipedia, $wikititle);
    if (empty($wiki['content'])) continue;
    $find_cnt++;
    $col->update(array('_id' => $value['_id']),
                 array('$set' => array('wikiContent' => $wiki['content'])),
                 array('safe' => true));
  }
}
// multiple
var_dump($find_cnt);

$col->update(array('wikiContent' => array('$exists' => false),
                   'desc' => array('$exists' => true)),
             array('$rename' => array('desc' => 'wikiContent')),
             array('safe' => true, 'multiple' => true));
             
$col->update(array('wikiContent' => array('$exists' => true),
                   'desc' => array('$exists' => true)),
             array('$unset' => array('desc' => '')),
             array('safe' => true, 'multiple' => true));

function get_wiki($col, $name, $nid = array())
{
  $query = array('title' => $name);
  if (!empty($nid)) $query['_id'] = array('$nin' => $nid);
  // find
  $wiki = $col->findOne($query);
  if (!empty($wiki) && !empty($wiki['content'])) {
    // 排除已经查过的
    $nid[] = $wiki['_id'];
    // 如果有跳转则递归
    if (preg_match('/#REDIRECT\s?(.+)/i', $wiki['content'], $match)) {
      $wiki = get_wiki($col, $match[1], $nid);
    }
  }
  return $wiki;
}