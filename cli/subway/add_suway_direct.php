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

$col = $tripfm->Subway;

$it = $col->find();

foreach (iterator_to_array($it) as $subway) {
  foreach ($subway['subline'] as &$subline) 
  foreach ($subline['list']   as &$station) {
    $station['direct'] = 0;
  }
  unset($subline, $station);
  $col->update(array('_id' => $subway['_id']),
               array('$set' => array('subline' => $subway['subline'])),
               array('safe' => true));
}