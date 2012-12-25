<?php
class Config extends Base_config {
  
  static $is_eng = true;
  
  static $region_name = 'Amsterdam';

  static $tpl_url = "http://en.wikipedia.org/w/index.php?title=Template:%s&action=edit";
  
  static $tpl_refer = "http://en.wikipedia.org/wiki/Template:%s";
  
  static $tpl_param = array(
    'get_parse_name' => array(
      'Amsterdam Metro Line 50 navbox',
      'Amsterdam Metro Line 51 navbox',
      'Amsterdam Metro Line 52 navbox',
      'Amsterdam Metro Line 53 navbox',
      'Amsterdam Metro Line 54 navbox',
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
      if (preg_match_all('#^\*\s+\[\[([^:;]+?)\|([^:;]+?)\]\]#', 
          $line_str, $matches)) break;   
      return false;
    } while(0);
    $wiki_title = trim($matches[1][0], '[]{}');
    $name       = trim($matches[2][0], '[]{}');
    // var_dump(array($wiki_title, $name)); exit;
    return true;
  } 
}
