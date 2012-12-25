 <?php
/**
 * config run
 * 
 * @author HouRui
 * @since 2012-04
 */
if(!defined('IN_SYSTEM')) {	exit('Access Denied'); }

$_SCONFIG = array();//INIT

/* 
 * needed extension 
 */
$_SCONFIG['extensions'] = array('pcntl', 'posix', 'gearman', 'memcached');

/*
 * gearman worker options 
 */
$_SCONFIG['gearman_worker_options'] = array(
	//'WorkerExtractArticleImage'  => 20,
	'WorkerDownloadImage'        => 30,
	//'WorkerSearchImage'	       => 1,
	//'WorkerDownloadGoogleImage'    => 10,
);

/*
 * listener functions
 */
$_SCONFIG['listener_functions'] = array(
	//'article_listener', 
	//'images_listener',
	//'google_search_images_listener',
	//'google_search_image_download_listener',
	//'google_search_image_download_listener_by_txt',
	'article_image_download_listener_by_txt',
);






