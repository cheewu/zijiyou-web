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
  $subway = get_init_subway($tripfm, $region, $line_name, Config::$tpl_refer);
  $subline = get_subline($tripfm, $line_name . '-1');
  
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
      
      $poi = get_poi($wiki_collection, $region, $name, $wiki_title);
      
      // check if poi exists
      $poi = check_poi_exists($tripfm, $poi);
      
      // push into subline
      $subline['list'][] = get_station($poi['_id'], count($subline['list']) + 1);
      
      // handle poi subway
      if(!$poi['subway']) $poi['subway'] = array();
      $idx_line_in_poi = $is_subline_in_poi = false;
      foreach ($poi['subway'] as $idx => $poi_subway) {
        if ($poi_subway['lineId'] != $subway['lineId']) continue;
        $is_line_in_poi = $idx;
        if (!$poi_subway['subline']) $poi_subway['subline'] = array();
        foreach ($poi_subway['subline'] as $poi_subline) {
          if ($poi_subline['id'] != $subline['id']) continue;
          $is_subline_in_poi = true;
        }
      }
      
      if ($idx_line_in_poi === false) {
        $poi_subway = array(
          'lineId' => $subway['lineId'],
          'subline' => array(array('id' => $subline['id'])),
        );
      }
      
      if ($is_subline_in_poi === false) {
        $poi['subway'][] = $poi_subway;
      }
      
      if (count($poi['subway']) > 1) {
        echo sprintf("'%s' has in %d lines\n", $poi['_id'], count($poi['subway']));
      }
      
      // update to POI
      $update = $poi; unset($update['_id']);
      $tripfm->POI->update(array('_id' => $poi['_id']), array('$set' => $update), array('safe' => true));
  
    }
  }
  
  $subway['stationCount'] = count($subline['list']);
  $subway['subline'][] = $subline;
  $tripfm->Subway->insert($subway, array('safe' => true));
  echo $line_name . " get " . $subway['stationCount'] . " stations" . PHP_EOL;
  // end @debug
  sleep(1);
}