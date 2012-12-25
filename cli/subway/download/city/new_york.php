<?php
class Config extends Base_config 
{
  static $explode_char = '#\|-#';
  
  static $region_name = 'New York City';
  
  static $tpl_url = "http://en.wikipedia.org/w/index.php?title=%s&action=edit&section=%d";
  
  static $tpl_refer = "http://en.wikipedia.org/wiki/%s#Stations";
  
  static $tpl_param = array(
    'get_parse_name' => array(
      array('1 (New York City Subway service)', 3),
      array('2 (New York City Subway service)', 4),
      array('3 (New York City Subway service)', 4),
      array('4 (New York City Subway service)', 4),
      array('5 (New York City Subway service)', 5),
      array('A (New York City Subway service)', 5),
      array('B (New York City Subway service)', 4),
      array('C (New York City Subway service)', 5),
      array('D (New York City Subway service)', 5),
      array('E (New York City Subway service)', 4),
      array('F (New York City Subway service)', 4),
      array('G (New York City Subway service)', 5),
      array('L (New York City Subway service)', 2),
      array('M (New York City Subway service)', 7),
      array('N (New York City Subway service)', 4),
      array('Q (New York City Subway service)', 10),
      array('R (New York City Subway service)', 4),
      array('Franklin Avenue Shuttle',          2),
      array('Rockaway Park Shuttle',            2),
    ),
    'get_parse_name_parallel' => array(
      array('6 (New York City Subway service)', 5),
      array('7 (New York City Subway service)', 6),
      array('J/Z (New York City Subway service)', 7),
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
    return self::_get_parse($line_str, $wiki_title, $name, 1);
  }

  /**
   * get parse name wiki_title & name
   * @param string $line_str
   * @param string &$wiki_title
   * @param string &$name
   */
  static function get_parse_name_parallel($line_str, &$wiki_title, &$name)
  {
    return self::_get_parse($line_str, $wiki_title, $name, 2);
  } 
  
  /**
   * get parse name wiki_title & name
   * @param string $line_str
   * @param string &$wiki_title
   * @param string &$name
   * @param int $piece_count
   */
  static function _get_parse($line_str, &$wiki_title, &$name, $piece_count)
  {
    $piece = explode("\n|", trim(trim($line_str), '|'));
    if (count($piece) < 2) return false;
    // var_dump($piece); sleep(1);
    do {
      if (preg_match_all('#\[\[([^:;]+?)\|([^:;]+?)\]\]#', 
          html_entity_decode($piece[$piece_count]), $matches)) break;   
      return false;
    } while(0);
    $wiki_title = trim($matches[1][0], '[]{}');
    $name       = trim($matches[2][0], '[]{}');
    // var_dump(array($wiki_title, $name)); sleep(1); return false;
    return true;
  }
}
