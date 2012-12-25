<?php
include 'new_include/function.php';
include 'new_include/base_config.php';

$debug = false; // @debug

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
$wiki_collection_name = Config::$is_eng ? 'WikipediaEn' : 'Wikipedia';
$wiki_collection = $tripfm->$wiki_collection_name;
if (empty($region)) die(Config::$region_name . " not exists\n");

foreach (Config::$tpl_param AS $func => $param_arr) foreach ($param_arr AS $param) {
  // @debug 
  if ($debug) { 
    $station = array();
  }
  // end @debug
  
  !is_array($param) && $param = array($param);
  $line_name = current($param);
  
  // init subway
  $station = array();
  
  // parse url
  $param = array_map('rawurlencode', $param);
  array_unshift($param, Config::$tpl_url);
  $url = call_user_func_array('sprintf', $param);
  
  // http request
  $content = get_http($url);
  foreach (preg_split(Config::$explode_char, $content) AS $line_str) {
    // init
    $wiki_title_arr = $name_arr = array();
    if (!Config::$func($line_str, $wiki_title_arr, $name_arr)) continue;
    if (!is_array($name_arr))       $name_arr       = array($name_arr);
    if (!is_array($wiki_title_arr)) $wiki_title_arr = array($wiki_title_arr);
    if (count($name_arr) != count($wiki_title_arr)) exit('name != wiki_titke');
    
    foreach (array_map(null, $name_arr, $wiki_title_arr) as $value) 
    {
      list($name, $wiki_title) = $value;
      $station[] = array($line_str, $wiki_title, $name);
    }
  }
  
  echo $line_name . " has " . count($station) . " stations\n";
}