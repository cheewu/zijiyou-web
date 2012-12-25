<!DOCTYPE html>
<html lang="zh">
  <head>
<?php 
Mf::$tpl->loadLib('jquery')
        ->loadLib('bootstrap')
        ->loadLink('main.css') 
        ->viewSource();
?>
    <title><?=$this->safeGet('TITLE', 'Edit BackEnd')?></title>
  </head>
  <body>
