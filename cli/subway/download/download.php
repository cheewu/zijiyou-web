<?php
include 'include/function.php';
include 'include/base_config.php';

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
  if ($debug) { // @debug 
    $station = array();
  }
  !is_array($param) && $param = array($param);
  $line_name = current($param);
  if (!$debug) // @debug
  $subway = get_init_subway($tripfm, $region, $line_name, Config::$tpl_refer);
  
  $param = array_map('rawurlencode', $param);
  array_unshift($param, Config::$tpl_url);
  $url = call_user_func_array('sprintf', $param);
  $content = get_http($url);
  foreach (preg_split(Config::$explode_char, $content) AS $line_str) {
    $wiki_title = $name = "";
    if (!Config::$func($line_str, $wiki_title, $name)) continue;
    $poi = get_poi($wiki_collection, $region, $name, $wiki_title);
    if ($debug) { // @debug
      $station[] = "\n$wiki_title\n$name\n";
      //var_dump(array($wiki_title, $name)); continue;
    }
    if (!$debug) // @debug
    $tripfm->POI->insert($poi, array('safe' => true));
    if (!$debug) // @debug
    $subway['stationList'][] = get_station($poi['_id'], count($subway['stationList']) + 1);
  }
  if ($debug) { // @debug 
    printf("%s: %d\n", $line_name, count($station));
    // array_map('var_dump', $station);
  }
  if (!$debug) // @debug
  $subway['numberOfStation'] = count($subway['stationList']);
  if (!$debug) // @debug
  $tripfm->Subway->insert($subway, array('safe' => true));
  if (!$debug) // @debug
  echo $line_name . " get " . $subway['numberOfStation'] . " stations" . PHP_EOL;
  sleep(1);
}
