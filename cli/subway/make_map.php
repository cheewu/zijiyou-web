<?php
$data = file_get_contents("fan2jian.txt");
$data = explode("\n", $data);
$data = array_filter($data, 'trim');
$fp = fopen("./map", "wb");
foreach ($data AS $trans) {
  $trans = str_replace("=", '', $trans);
  $ucs2  = iconv("utf-8", "ucs-2", $trans);
  fwrite($fp, $ucs2);
}