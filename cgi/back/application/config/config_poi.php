<?php
return array(
  /**
   * @var display list fields
   */
  'list_fields' => array(
    'area',
	'name',
    'rank',
	'englishName',
	'wikititle',
    'center',
	'ticket',
	'telNum',
	'openTime',
  ),
  
  /**
   * @var sort field
   */
  'sort_fields' => array('name', 'rank'),
  
  /**
   * @var default sort
   */
  'default_sort' => array('rank' => -1),
  
  /**
   * @var edit fields
   */
  'edit_fields' => array(
    'area', 'name', 'englishName', 'wikititle', 'ticket',
	'telNum', 'keyword', 'website', 'blog', 'weibo', 'openTime', 'rank'
  ),
);