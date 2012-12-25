<?php
$region_name = 'Paris';

// this system DB
$config = array(
  'server'  => 'mongodb://202.85.213.54:27017',
  'options' => array('username' => 'admin',
                     'password' => 'iamzijiyou',
                     'connect'  => true),
);

$con = new Mongo($config['server'], $config['options']);

$tripfm  = $con->tripfm;
$wikiCol = $tripfm->WikipediaEn;

$region = $tripfm->Region->findOne(array('englishName' => $region_name));

if (empty($region)) die("region name: $region_name is wrong" . PHP_EOL);

$line_url_arr = array(
//  'Paris Métro Line 1',
//  'Paris Métro line 2',
//  'Paris Métro Line 3',
//  'Paris Métro Line 3bis',
//  'Paris Metro Line 4',
//  'Paris Métro Line 5',
//  'Paris Métro Line 6',
//  'Paris Métro Line 7',
//  'Paris Métro Line 7bis',
//  'Paris Métro Line 8',
//  'Paris Métro Line 9',
//  'Paris Métro Line 10',
//  'Paris Métro Line 11',
//  'Paris Métro Line 12',
//  'Paris Métro Line 13',
//  'Paris Métro Line 14', 
//  'RER_A',
//  'RER_B',
//  'RER_C',
//  'RER_D',
//  'RER_E',
  'Île-de-France_tramway_Line_1',
  'Île-de-France_tramway_Line_2',
  'Île-de-France_tramway_Line_3',
  'Île-de-France_tramway_Line_4',
);

$url_tpl = "http://en.wikipedia.org/w/index.php?title=Template:%s&action=edit";

foreach ($line_url_arr AS $var) {
  $subway = get_init_subway($tripfm, $region, $var);
  $url = sprintf($url_tpl, rawurlencode($var));
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_AUTOREFERER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)');
  curl_setopt($ch, CURLOPT_HEADER, false);
  $content = curl_exec($ch);
  curl_close($ch); unset($ch);
  preg_match_all('#^.*?BHF.*?$#m', $content, $matches);
  var_dump($matches);
  foreach ($matches[0] AS $line_str) {
    if (!get_parse_name($line_str, $wiki_title, $name)) continue;
    $poi = get_poi($wikiCol, $region, $name, $wiki_title);
    var_dump($wiki_title, $name); continue;
    //$tripfm->POI->insert($poi, array('safe' => true));
    $subway['stationList'][] = get_station($poi['_id'], count($subway['stationList']) + 1);
  }
  $subway['numberOfStation'] = count($subway['stationList']);
  //$tripfm->Subway->insert($subway, array('safe' => true));
  echo $var . " get " . $subway['numberOfStation'] . " stations" . PHP_EOL;
  sleep(1);
}

/**
 * get mongo increment
 * @param string $dbname
 * @param string $collection
 * @param string field
 * @return int
 */
function get_mongo_increment(MongoDB $db, $collection, $field) 
{
  $cmd = array(
    'findAndModify' => 'autoIncrement',
    'query' => array('field' => $field, 'collection' => $collection),
    'update' => array('$inc' => array('index' => 1)),
    'new' => true,
  );
  $res = $db->command($cmd);
  return $res['value']['index'];
}

/**
 * 从wikipedia集合中找content信息
 * @param string $name
 * @return array()
 */
function get_en_wiki_center(MongoCollection $collection, $wiki_title) 
{
  $query = array('title' => $wiki_title);
  $wiki = $collection->findOne($query, array('center'));
  return !empty($wiki['center']) ? $wiki['center'] : false;
}

/**
 * init subway
 * @param string $name
 * @param array $region
 */
function get_init_subway(MongoDB $db, $region, $name)
{
  return array(
    'region'          => $region['englishName'],
    'regionId'        => $region['_id'],
    'system'          => '',
    'name'            => $name,
    'color'           => '',
    'lineid'          => get_mongo_increment($db, 'Subway', 'lineid'),
    'numberOfStation' => 0,
    'length'          => 0,
    'stationList'     => array(),
    'wiki'            => "http://en.wikipedia.org/wiki/Template:$name",
  );
}

/**
 * get poi
 * @param MongoCollection $wiki_collection
 * @param array $region
 * @param string $name
 * @param string $wiki_title
 */
function get_poi(MongoCollection $wiki_collection, $region, $name, $wiki_title)
{
  return array(
    'area'      => $region['englishName'],
    'name'      => $name,
    'regionId'  => $region['_id'],
    'cateogry'  => 'subway',
    'center'    => get_en_wiki_center($wiki_collection, $wiki_title),
    'line'      => array(),
    'wikititle' => $wiki_title,
  );
}

/**
 * get station
 * @param MongoId $poiId
 * @param int $station_order
 */
function get_station(MongoId $poiId, $station_order)
{
  return array(
    'poiId'         => $poiId,
    'stationOrder'  => $station_order,
    'stationMinute' => 0,
    'transferLine'  => array(),
  );
}

/**
 * get parse name wiki_title & name
 * @param string $line_str
 * @param string &$wiki_title
 * @param string &$name
 */
function get_parse_name($line_str, &$wiki_title, &$name)
{
  if (!preg_match('#^\{\{BS(\d*)#', $line_str, $matches)) return false;
  $n = intval(@$matches[1][0]);
  $c = !$n ? '' : "$n";
  !$n && $n = 1;
  do {
    if (preg_match_all("#\{\{BS{$c}(\|[^\|]*){{$n}}\|\|[\{\[]{2}BSto\|([^\{\[]+?)\|([^\{\[]+?)[\}\]]{2}#", 
        $line_str, $matches)) break;
    if (preg_match_all("#\{\{BS{$c}(\|[^\|]*){{$n}}\|\|[\{\[]{2}([^\{\[]+?)\|([^\{\[]+?)[\}\]]{2}#", 
        $line_str, $matches)) break;
    if (preg_match_all("#\{\{BS{$c}(\|[^\|]*){{$n}}\|\|[\{\[]*([^\{\[]+?)[\{\[]*\|#", 
        $line_str, $matches)) break;   
    return false;
  } while(0);
  $match_cnt = count($matches);
  $wiki_title = trim($matches[$match_cnt > 3 ? $match_cnt - 2 : $match_cnt - 1][0], '[]{}');
  $name       = trim($matches[$match_cnt - 1][0], '[]{}');
  if ($name == 'under construction') return false;
  return true;
}
