<?php
require_once 'common.php';
ini_set('memory_limit', '512M');

filter_images("/sdb2/articleOriginal3", "/sdb2/article2");
//article_update_images_status("/sdb2/article");
exit;

article_image_download_listener_by_txt();
exit;

google_search_image_download_listener_by_txt();
exit;
cp_image('/sdb2/googleImages/POI',    '/sdb2/googleImagesWithSuffix/POI');
cp_image('/sdb2/googleImages/Region', '/sdb2/googleImagesWithSuffix/Region');
//exit;
filter_images(S_IMG, S_ROOT.'image', '/sdb2/article');
//scan_stats(S_IMG, S_ROOT.'image');
//_images_scan_exists_file_count(S_IMG, S_ROOT.'image');
exit;


$_SGLOBAL['db'] = new MongoHandle($_SC['MongoDB']['server'], $_SC['MongoDB']['dbname'], $_SC['MongoDB']['options']);

$article = $_SGLOBAL['db']->Article_select_one(array('_id' => new MongoID('4f466821e77989786c000008')), array('content'));
		
preg_replace_callback("#<\s*img.*?real_src\s*=\s*[\"']([^\"]*)[\"'].*?/\s*>#", 
		function($matches) {return "<img src='$matches[1]' />";}, 
		$article['content']);
		
preg_match_all("#<\s*img.*?src\s*=\s*[\"']([^\"]*)[\"'].*?/\s*>#", $article['content'], $matches);

$images = $matches[1];

foreach ($images AS $image) {
	$image = trim($image);
	$md5 = md5($image);
	$is_exists = $_SGLOBAL['db']->Images_select_one(array('md5' => $md5));
	if(!empty($is_exists)) { continue; } 
	$res = $_SGLOBAL['db']->Images_insert(array('url' => $image, 'md5' => $md5));
	var_dump($res);
}
exit;


