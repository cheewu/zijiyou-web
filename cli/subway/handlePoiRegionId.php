<?php
include 'common.php';

$con = $_SGLOBAL['db']->con;

$tripfm = $con->tripfm;

$it = $tripfm->POI->find(array('regionId' => '4e8c091fd0c2ff482300031d'), array('regionId'));

$arr = array_values(iterator_to_array($it));

foreach ($arr AS $poi) {
  $tripfm->POI->update(array('_id' => $poi['_id']), array('$set' => array('regionId' => new MongoId(strval($poi['regionId'])))), array('safe' => true));
}