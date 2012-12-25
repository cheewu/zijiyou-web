<?php
foreach ($this['fields'] AS $field) {
  if (!in_array($field, $this['sort_fields'])) {
    echo sprintf('<th class="list-%s">%s</th>', $field, $field);
  } else {
    if ($this['sort']['field'] != $field) {
      $icon = '';
      $order = '-';
    } else {
      $order = ($this['sort']['order'] == -1) ? '+' : '-';
      $icon  = ($this['sort']['order'] == -1) 
             ? '<i class="icon-arrow-down"></i>' 
             : '<i class="icon-arrow-up"></i>' ;
    }
    $href = Mf::$url->makeOrigin(array('sort' => "$order" . "$field"));
    echo sprintf('<th class="list-%s"><a href="%s">%s%s</a></th>', 
                 $field, $href, $field, $icon);
  }
} 
