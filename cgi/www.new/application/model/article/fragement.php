<?php
$region_id = $_GET['region_id'];
$keywordsParagraph_id = $_GET['fragement_id'];

$region = $_SGLOBAL['db']->Region_select_one(array('_id' => new MongoID($region_id)));
$keywordsParagraph = $_SGLOBAL['pagedb']->keywordsParagraph_select_one(array('_id' => new MongoID($keywordsParagraph_id)));
$selected_paragraphs_all = $_SGLOBAL['pagedb']->articleParagraphs_select_one(
							array('documentID' => $keywordsParagraph['documentID']),
						    array('paragraphs')
						);
						
$selected_paragraphs = array();

foreach ($keywordsParagraph['paragraphs'] AS $index => $paragraph) {
	$paragraph_start = $paragraph['start'];
	$paragraph_end   = $paragraph['end'];
	for ($i = $paragraph_start; $i <= $paragraph_end; ++$i) {
		$paragraph_one = $selected_paragraphs_all['paragraphs'][$i];
		$selected_paragraphs[$i] = $paragraph_one;
	}
}

ksort($selected_paragraphs);

$filter_paragraph_arr = array();

foreach ($selected_paragraphs AS $index => $paragraph) {
    $paragraph = tpl_trim($paragraph);
    $paragraph = tpl_src_cure($paragraph, 620, 0);
    $paragraph = preg_replace("#(<\s*img.*?/\s*>)#", 
                         "@^@\$1@^@", $paragraph);
    $paragraph_piece_arr = array_filter(array_map('tpl_trim', explode("@^@", $paragraph)));
    foreach ($paragraph_piece_arr AS $paragraph_piece) {
        !empty($paragraph_piece) && $filter_paragraph_arr[] = "<p a-id={$article_id}_{$index}>$paragraph_piece</p>";
    }
}

$seelcted_fragement = implode("", $filter_paragraph_arr);

$article = $_SGLOBAL['pagedb']->Article_select_one(
					array('_id' => new MongoID($keywordsParagraph['documentID'])),
					array('title', 'optDateTime', 'author')
				);
unset($article['_id']);
$fragement = array_merge($keywordsParagraph, $article);

/*
$seelcted_fragement = '<p>'.implode('</p><p>', $selected_paragraphs).'</p>';
$seelcted_fragement = preg_replace("#<\s*img.*?real_src\s*=\s*[\"']([^\"]*)[\"'].*?/\s*>#", '<img src="$1" />', $seelcted_fragement);
$seelcted_fragement = preg_replace_callback("#<\s*img.*?src\s*=\s*[\"']([^\"]*)[\"'].*?/\s*>#", 
	create_function('$matches', "return '<img src=\"'.img_proxy(\$matches[1], '{$fragement['url']}', 620, 0).'\" onerror=\"\$(this).css(\'display\', \'none\')\"/>';"), $seelcted_fragement);
*/




$_TPL['title'] = @$fragement['title']."_游记";
include template();
return;





foreach($selected_num_arr AS $select_one) {
	$item = trim($fragement['fragment'][$select_one]);
	!empty($item) && $seelcted_fragement .= "<p>$item</p>";
}
$seelcted_fragement = preg_replace("#<\s*img.*?real_src\s*=\s*[\"']([^\"]*)[\"'].*?/\s*>#", '<img src="$1" />', $seelcted_fragement);
$seelcted_fragement = preg_replace_callback("#<\s*img.*?src\s*=\s*[\"']([^\"]*)[\"'].*?/\s*>#", 
	create_function('$matches', "return '<img src=\"'.img_proxy(\$matches[1], '{$fragement['url']}', 620, 0).'\" onerror=\"\$(this).css(\'display\', \'none\')\"/>';"), $seelcted_fragement);



include template();