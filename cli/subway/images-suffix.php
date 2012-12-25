<?php
ini_set('memory_limit', '512M');

$db_conf = array(
	'server'	=> 'mongodb://202.85.213.54:27017',
	'options'	=> array('username' => 'admin', 'password' => 'iamzijiyou'),
);

$con = new Mongo($db_conf['server'], $db_conf['options']);

$tripfm = $con->tripfm;

$it = $tripfm->POI->find(array('googleImages' => array('$exists' => true)), 
                         array('googleImages'));
$poi_arr = array_values(iterator_to_array($it));
if (empty($poi_arr)) break;
foreach ($poi_arr AS $poi) {
  var_dump($poi);
  exit;
}
