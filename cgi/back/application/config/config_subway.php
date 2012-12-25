<?php
return array(
  /**
   * @var display list fields
   */
  'list_fields' => array(
    'regionName', 
    'name', 
    'lineId',
    'stationCount', 
    'link', 
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
    'color', 'name', 'system'
  ),
);