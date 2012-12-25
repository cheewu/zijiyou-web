<?php
var_dump(fan2jian("荃灣綫"));
function fan2jian($fan_str, $encoding = 'utf-8')
{
  static $map;
  if (empty($map)) { 
    $data = file_get_contents(dirname(__FILE__) . '/gbbig.map');
    for ($i = 0; $i < strlen($data); $i = $i + 4) {
      $trans = iconv("ucs-2", "utf-8", substr($data, $i, $i + 4));
      $map[mb_substr($trans, 1, 1, 'utf-8')] = mb_substr($trans, 0, 1, 'utf-8');
    }
  }
  $str = @iconv($encoding, "utf-8//ignore", $fan_str);
  if (!$str) return "";
  $utf8_cnt = mb_strlen($str, "utf-8");
  for ($jian_str = "", $i = 0; $i < $utf8_cnt; $i++) {
    $char_utf8 = mb_substr($str, $i, 1, "utf-8");
    if (strlen($char_utf8) < 2 || !isset($map[$char_utf8])) { 
      $jian_str .= $char_utf8; continue; 
    }
    $jian_str .= $map[$char_utf8];
  }
  return $jian_str;
}
