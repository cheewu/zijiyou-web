<?php
$region_id = $_GET['region_id'];
$article_id = $_GET['article_id'];

$region = $_SGLOBAL['db']->Region_select_one(array('_id' => new MongoID($region_id)));
$article = $_SGLOBAL['pagedb']->articleParagraphs_select_one(array('documentID' => $article_id));

$img_width = (basename(T) == 'i') ? 580 : 620;

$paragraph_arr = array();
foreach ($article['paragraphs'] as $index => $paragraph) {
  $paragraph = tpl_trim($paragraph);
  $paragraph = tpl_src_cure($paragraph, $img_width, 0);
  $paragraph = preg_replace("#(<\s*img.*?/\s*>)#", "@^@\$1@^@", $paragraph);
  $paragraph_piece_arr = array_filter(
  array_map('tpl_trim', explode("@^@", $paragraph)));
  foreach ($paragraph_piece_arr as $paragraph_piece) {
    if (empty($paragraph_piece)) continue;
    $a_id = "{$article_id}_{$index}";
    $paragraph_arr[] = array($a_id, $paragraph_piece);
  }
}

//$content = implode("", $filter_paragraph_arr);

$_TPL['title'] = @$article['title']."_游记";

include template();