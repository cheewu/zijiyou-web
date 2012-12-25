<?php
require_once 'function_common.php';
require_once 'MongoDB.php';

// this system DB
$_SC['MongoDB'] = array(
	'server'	=> 'mongodb://202.85.213.54:27017',
	'dbname'	=> 'tripfm',
	'options'	=> array('username' => 'admin', 
	                     'password' => 'iamzijiyou', 
                         'connect'  => true),
);

$_SGLOBAL['db'] = new MongoHandle($_SC['MongoDB']['server'], $_SC['MongoDB']['dbname'], $_SC['MongoDB']['options']);