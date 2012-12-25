<?php
class Config extends Base_config {
  
  static $region_name = 'Rome';
  
  static $tpl_param = array(
    'get_parse_name' => array(
      'Rome Metro Line A',
      'Rome Metro Line B',
      'Rome Metro Line C',
    )
  );
  
  /**
   * get parse name wiki_title & name
   * @param string $line_str
   * @param string &$wiki_title
   * @param string &$name
   */
  static function get_parse_name($line_str, &$wiki_title, &$name)
  {
    do {
      if (preg_match_all('#\[\[([^:;]+?)\|([^:;]+?)\]\]#', $line_str, $matches)) break;   
      return false;
    } while(0);
    $wiki_title = trim($matches[1][0], '[]{}');
    $name       = trim($matches[2][0], '[]{}');
    return true;
  } 
}
