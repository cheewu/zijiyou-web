<?php
class Config extends Base_config {
  
  static $explode_char = '#\|-#';
  
  static $region_name = 'Singapore';

  static $tpl_url = "http://en.wikipedia.org/w/index.php?title=%s&action=edit&section=%d";
  
  static $tpl_refer = "http://en.wikipedia.org/wiki/%s#Stations";
  
  static $tpl_param = array(
    'parse_list' => array(
      array('North South MRT Line', 8),
      array('East West MRT Line',   7),
      array('North East MRT Line',  3),
      array('Circle MRT Line',      7),
      array('Downtown MRT Line',    7),
      array('Thomson MRT Line',     7),
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
    $piece = explode("||", trim(trim($line_str), '|'));
    if (count($piece) != 3) return false;
    do {
      if (preg_match_all('#\[\[([^:;]+?)\|([^:;]+?)\]\]#', 
          html_entity_decode($piece[1]), $matches)) break;   
      return false;
    } while(0);
    $wiki_title = trim($matches[1][0], '[]{}');
    $name       = trim($matches[2][0], '[]{}');
    // var_dump(array($wiki_title, $name)); exit;
    return true;
  }
}
