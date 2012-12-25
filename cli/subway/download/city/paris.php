<?php
class Config extends Base_config {
  
  static $region_name = 'Paris';
  
  static $tpl_param = array(
    'get_parse_name' => array(
//      'Paris Métro Line 1',
//      'Paris Métro line 2',
//      'Paris Métro Line 3',
//      'Paris Métro Line 3bis',
//      'Paris Metro Line 4',
//      'Paris Métro Line 5',
//      'Paris Métro Line 6',
//      'Paris Métro Line 7',
//      'Paris Métro Line 7bis',
//      'Paris Métro Line 8',
//      'Paris Métro Line 9',
//      'Paris Métro Line 10',
//      'Paris Métro Line 11',
//      'Paris Métro Line 12',
//      'Paris Métro Line 13',
//      'Paris Métro Line 14', 
    ),
    'parse_rer' => array(
      'RER_A',
      'RER_B',
      'RER_C',
      'RER_D',
      'RER_E',
      'Île-de-France_tramway_Line_1',
      'Île-de-France_tramway_Line_2',
      'Île-de-France_tramway_Line_3',
      'Île-de-France_tramway_Line_4',
    ),
  );
  
  /**
   * get parse name wiki_title & name
   * @param string $line_str
   * @param string &$wiki_title
   * @param string &$name
   */
  static function get_parse_name($line_str, &$wiki_title, &$name)
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
  
  /**
   * get parse name wiki_title & name
   * @param string $line_str
   * @param string &$wiki_title
   * @param string &$name
   */
  static function parse_rer($line_str, &$wiki_title, &$name)
  {
    $type_cnt = 0;
    if (preg_match("#BS-(header|table)#i", $line_str)) return false;
    if (!preg_match('#^\{\{BS(\d*)#i', $line_str, $matches)) return false;
    else {
      $type_cnt = isset($matches[1][0]) ? intval($matches[1][0]) : 1;
      $pieces_cnt = $type_cnt + 3;
      $pieces   = explode("|", trim($line_str, '{}'), $pieces_cnt);
    }
    if (!preg_match('#^\{\{BS(\d*)-(\d+)#i', $line_str)) {
      if (empty($pieces)) return false;
    } else {
      $type_cnt = isset($matches[1][0]) ? intval($matches[1][0]) : 1;
      $pieces_cnt = $type_cnt + 2;
      $pieces   = explode("|", trim($line_str, '{}'), $pieces_cnt);
    } 
    $element = end($pieces);
    if (count($pieces) != $pieces_cnt) return false;
    do {
      if (preg_match('#O\d=#', $element)) return false;
      if (substr($element, 0, 5) == 'uexAB') return false;
      if (preg_match_all('#\[\[([^:;]+?)\|([^:;]+?)\]\]#', 
          html_entity_decode($element), $matches)) {
        $wiki_title = array_map(function($v){
          return trim($v, '[]{}');
        }, $matches[1]);
        $name       = array_map(function($v){
          return trim($v, '[]{}');
        }, $matches[2]);      
        break;
      }
      if (preg_match_all('#\[\[([^:;<>=\|\{\}]+)\]\]\|?#', 
          html_entity_decode($element), $matches)) {
        $wiki_title = $name = array_map(function($v){
          return trim($v, '[]{}');
        }, $matches[1]);
        break;
      }
      if (preg_match_all('#([^:;<>=\}\{\|]+)(\|)?.*$#', 
          html_entity_decode($element), $matches)) {
        $name = array_map(function($v){
          return trim($v, '[]{}');
        }, $matches[1]);
        $wiki_title = array_fill(0, count($name), "");
        break;
      } 
      return false;
    } while(0);
    //var_dump(array($matches[1], $matches[2])); // exit;
    return true;
  } 
}
