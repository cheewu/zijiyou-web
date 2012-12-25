<?php
function fan2jian($fan_str, $encoding = 'utf-8')
{
  static $map;
  if (empty($map)) { 
    $data = file_get_contents(dirname(__FILE__) . '/gbbig.map');
    for ($i = 0; $i < strlen($data); $i = $i + 4) {
      $trans = iconv("ucs-2", "utf-8", substr($data, $i, $i + 4));
      $map[mb_substr($trans, 1, 1, 'utf-8')] = mb_substr($trans, 0, 1, 'utf-8');
    }
  }
  $str = @iconv($encoding, "utf-8//ignore", $fan_str);
  if (!$str) return "";
  $utf8_cnt = mb_strlen($str, "utf-8");
  for ($jian_str = "", $i = 0; $i < $utf8_cnt; $i++) {
    $char_utf8 = mb_substr($str, $i, 1, "utf-8");
    if (strlen($char_utf8) < 2 || !isset($map[$char_utf8])) { 
      $jian_str .= $char_utf8; continue; 
    }
    $jian_str .= $map[$char_utf8];
  }
  return $jian_str;
}

/**
 * 从wikipedia集合中找content信息
 * @param string $name
 * @return array()
 */
function get_wiki_center($name, $id = array()) {
	global $_SGLOBAL;
	$search = array('title' => $name);
	!empty($id) && $search['_id'] = array('$nin' => $id);
	//查询
	$wiki = $_SGLOBAL['db']->Wikipedia_select_one($search, array('center', 'content'));
	if(!empty($wiki) && !empty($wiki['center'])){
		//排除已经查过的
		$id[] = $wiki['_id'];
		//如果有跳转则递归
		if(preg_match("/#REDIRECT\s?(.+)/i", $wiki['content'], $match)){
			$wiki = get_wiki_content($match[1], $id);
		}
	}
	return isset($wiki['center']) ? $wiki['center'] : false;
}

/**
 * get mongo increment
 * @param string $dbname
 * @param string $collection
 * @param string field
 * @return int
 */
function get_mongo_increment($dbname, $collection, $field) {
  global $_SGLOBAL;
  $con = $_SGLOBAL['db']->con;
  $cmd = array(
    'findAndModify' => 'autoIncrement',
    'query' => array('field' => $field, 'collection' => $collection),
    'update' => array('$inc' => array('index' => 1)),
    'new' => true,
  );
  $res = $con->$dbname->command($cmd);
  return $res['value']['index'];
}