<?php
foreach (array('Region', 'POI', 'Subway') AS $col) { 
  $active = '';
  if ($this['col'] == $col) {
    $href = '#'; $active = 'active';
  } 
  if ($col == 'Region') $href = "/list/$col";
  //else if (!isset($this['rid'])) continue;
  else $href =  Mf::$url->makeBasic("/list/" . strtolower($col));
  echo sprintf('<li class="%s"><a href="%s">%s</a></li>',
               $active, $href, $col);
}