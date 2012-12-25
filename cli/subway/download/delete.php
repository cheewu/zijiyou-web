<?php
include 'new_include/function.php';
include 'new_include/base_config.php';

$opt = getopt('c:');
if (empty($opt['c'])) get_help();
$config_file = $opt['c'] . '.php';
if (!is_file('city/' . $config_file)) get_help();
include 'city/' . $config_file;

$tripfm = get_con()->tripfm;
$region = $tripfm->Region->findOne(
             array(Config::$is_eng ? 'englishName' : 'name' => 
                   Config::$region_name)
          );
if (empty($region)) die(Config::$region_name . " not exists\n");

$remove_line = $tripfm->Subway->remove(array('regionId' => $region['_id']), 
                                       array('safe' => true));
$remove_station = $tripfm->POI->remove(array('regionId' => $region['_id'], 
                                             'category' => 'subway'), 
                                       array('safe' => true));
                                       
printf("remove line: %d\nremove station: %d\n", $remove_line['n'], $remove_station['n']);
