<?php
class Config extends Base_config 
{
  static $region_name = 'London';
  
  static $tpl_param = array(
    'get_parse_name' => array(
      'Bakerloo Line RDT',
      'Central Line RDT',
      'Circle_Line_RDT',
      'District Line RDT',
      'Hammersmith & City line RDT',
      'Jubilee Line RDT',
      'Metropolitan Main Line RDT',
      'Northern Line map',
      'Piccadilly Line RDT',
      'Victoria Line RDT',
      'Waterloo & City line RDT',
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
}

