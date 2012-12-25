<?php
class Config extends Base_config 
{
  static $region_name = 'Barcelona';
  
  static $tpl_param = array(
    'parse_list' => array(
      'Barcelona Metro line 1',
      'Barcelona Metro line 2',
      'Barcelona Metro line 3',
      'Barcelona Metro line 4',
      'Barcelona Metro line 5',
      'Barcelona Metro line 6',
      'Barcelona Metro line 7',
      'Barcelona Metro line 9',
      'Barcelona Metro line 10',
      'Barcelona Metro line 11',
    ),
    'parse_map' => array(
      'Funicular de Montjuïc Line'
    ),
    'parse_parallel_map' => array(
      'Barcelona Metro Line 8 route map'
    ),
  );
  
  /**
   * get parse name wiki_title & name
   * @param string $line_str
   * @param string &$wiki_title
   * @param string &$name
   */
  static function parse_list($line_str, &$wiki_title, &$name)
  {
    do {
      if (preg_match_all('#^\*\s+\[\[([^:;]+?)\|([^:;]+?)\]\]#', 
          $line_str, $matches)) break;   
      return false;
    } while(0);
    $wiki_title = trim($matches[1][0], '[]{}');
    $name       = trim($matches[2][0], '[]{}');
    // var_dump(array($wiki_title, $name)); exit;
    return true;
  }
  
  /**
   * get parse name wiki_title & name
   * @param string $line_str
   * @param string &$wiki_title
   * @param string &$name
   */
  static function parse_map($line_str, &$wiki_title, &$name)
  {
    if (preg_match("#BS-(header|table)#i", $line_str)) return false;
    if (!preg_match('#^\{\{BS(\d*)#i', $line_str, $matches)) return false;
    $type_cnt = isset($matches[1][0]) ? intval($matches[1][0]) : 1;
    $pieces   = explode("|", trim($line_str, '{}'), $type_cnt + 3);
    do {
      if (preg_match_all('#\[\[([^:;]+?)\|([^:;]+?)\]\]#', 
          html_entity_decode(array_pop($pieces)), $matches)) break;   
      return false;
    } while(0);
    $wiki_title = trim($matches[1][0], '[]{}');
    $name       = trim($matches[2][0], '[]{}');
    // var_dump(array($wiki_title, $name)); exit;
    return true;
  }
  
  /**
   * get parse name wiki_title & name
   * @param string $line_str
   * @param string &$wiki_title
   * @param string &$name
   */
  static function parse_parallel_map($line_str, &$wiki_title, &$name)
  {
    if (preg_match("#BS-(header|table)#i", $line_str)) return false;
    if (!preg_match('#^\{\{BS(\d)-(\d)#i', $line_str, $matches)) return false;
    $type_cnt = isset($matches[1][0]) ? intval($matches[1][0]) : 1;
    $type_cnt += intval($matches[2][0]);
    $pieces   = explode("|", trim($line_str, '{}'), $type_cnt + 1);
    if (count($pieces) < $type_cnt) return false;
    do {
      if (preg_match_all('#\[\[([^:;\{\}]+?)\|([^:;\{\}]+?)\]\]#', 
          html_entity_decode(array_pop($pieces)), $matches)) break;   
      return false;
    } while(0);
    if (count($matches[0]) > 1) return false;
    $wiki_title = trim($matches[1][0], '[]{}');
    $name       = trim($matches[2][0], '[]{}');
    // var_dump(array($wiki_title, $name)); exit;
    return true;
  }
}
