<?php

return array(
  /**
   * @var display list fields
   */
  'list_fields' => array(
    'area', 
    'name', 
    'center',
    'poiCnt',
    'englishName', 
    'wikititle', 
  ),
  
  /**
   * @var sort field
   */
  'sort_fields' => array('name', 'area'),
  
  /**
   * @var default sort
   */
  'default_sort' => array('name' => -1),
  
  /**
   * @var edit fields
   */
  'edit_fields' => array(
    'area', 'name', 'englishName', 'wikititle', 'keyword', 
    'website', 'blog', 'weibo', 'openTime', 'poi_cnt', 'is_important'
  ),
);