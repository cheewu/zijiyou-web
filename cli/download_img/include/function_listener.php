<?php

/**
 * listen article
 */
function article_listener() {

	$db = connect_mongodb();
	
	$gearman_client = gearman_get_client();
//	$seek_file = S_DAT.'article.txt';
//	
//	if(is_file($seek_file)) {
//		$tmp_article_id = file_get_contents($seek_file);
//		if(strlen($tmp_article_id) == 24) {
//			$article_id = $tmp_article_id;
//		}
//	}
	
	$per_page = 5000;
	$skip = 0;
	while (1) {
		if(!$db->con->connected) {
			$db = connect_mongodb();
		}
		$res = $db->db->Article->find(array(), array('_id', 'is_extract_image'))->sort(array('_id' => 1))->skip($skip)->limit($per_page);
		$res = array_values(iterator_to_array($res));
		foreach ($res AS $article) {
			if ($article['is_extract_image']) { continue; }
			if (gearman_client_dobackground($gearman_client, 'extract_article_image', strval($article['_id']))) {
				$db->Article_update(array('is_extract_image' => true), array('_id' => $article['_id']));
			}
		}
		if (empty($res)) {
			sleep(1);
			$skip = 0;
		} else {
			$skip += $per_page;
		}
	}
}


/**
 * listen images
 */
function images_listener() {
	ini_set('memory_limit', '256M');
	global $_SC, $_SGLOBAL;
	$db = connect_mongodb();
	
//	$res = $db->db->Images->find(array('_id' => array('$gt' => new MongoID("4fd6d4c6c46988845d0002f2"))))->sort(array('_id' => 1))->limit(5000);
//	var_dump($res);
//	$res = array_values(iterator_to_array($res));
//	var_dump($res);
//	var_dump(empty($res));
//	exit;
	
	
	$memcached = new Memcached();
	$memcached->addServers($_SC['memcached_server']);
	
	$gearman_client = gearman_get_client();
	_images_scan_exists_file(S_IMG, S_ROOT.'image');
	
	list($gearman_ip, $gearman_port) = explode(":", $_SC['gearman_server']);
	$gs = new GearmanServerStatus($gearman_ip, $gearman_port);
	
	while (1) {
		$tmp_res = $gs->getStatus();
		if (!isset($tmp_res['operations']['download_image']['total'])) { break; }
		$count = $tmp_res['operations']['download_image']['total'];
		if ($count > 0) {
			$_SGLOBAL['logger']->info("gearman queue count: $count"); 
			sleep(5); continue; 
		}
		break;
	}
	
	$prefix = "img_";
	$per_page = 50000;
	$query = array();
	
	do {
		if(!$db->con->connected) {
			$db = connect_mongodb();
		}
		$res = $db->db->Images->find($query)->sort(array('md5' => 1))->limit($per_page);
		$res = array_values(iterator_to_array($res));
		foreach ($res AS $image) {
			$query = array('md5' => array('$gt' => $image['md5']));
			$exists = $memcached->get($prefix.$image['md5']);
			if (!empty($exists)) { continue; }
			gearman_client_dobackground($gearman_client, 'download_image', serialize($image));
		}
		$_SGLOBAL['logger']->info(strval("md5: ".$image['md5'])." query");
		if (count($res) < $per_page) { break; }
	} while (1);
	$_SGLOBAL['logger']->info("query finish");
	
	while (1) {
		$tmp_res = $gs->getStatus();
		$count = $tmp_res['operations']['download_image']['total'];
		if ($count > 0) {
			$_SGLOBAL['logger']->info("gearman queue count: $count"); 
			sleep(5); continue; 
		}
		exit;
	} 
}


function google_search_images_listener() {
	ini_set('memory_limit', '512M');
	global $_SC, $_SGLOBAL;
	
	$memcached = new Memcached();
	$memcached->addServers($_SC['memcached_server']);
	
	$exists = $memcached->get('search_ip');
	if (empty($exists)) { 
		$memcached->set('search_ip', 3232235521);
	}
	
	
	$db = connect_mongodb('tripfm');
	
	$gearman_client = gearman_get_client();
	
	list($gearman_ip, $gearman_port) = explode(":", $_SC['gearman_server']);
	$gs = new GearmanServerStatus($gearman_ip, $gearman_port);
	
	while (1) {
		$tmp_res = $gs->getStatus();
		if (!isset($tmp_res['operations']['search_image']['total'])) { break; }
		$count = $tmp_res['operations']['search_image']['total'];
		if ($count > 0) {
			$_SGLOBAL['logger']->info("gearman queue count: $count"); 
			sleep(5); continue; 
		}
		break;
	}
	/*
	$important_region = $db->Region_select(array('is_important' => true), array('_id'));
	foreach ($important_region AS $region) {
		$pois = $db->POI_select(array('regionId' => $region['_id']), array('name', 'googleImages'), array('rank' => -1), 30);
		foreach ($pois AS $poi) {
			if (!empty($poi['googleImages'])) { continue; }
			$ip = $memcached->get('search_ip');
			$poi['ip'] = long2ip($ip);
			gearman_client_dobackground($gearman_client, 'search_image', serialize($poi));
			$memcached->increment('search_ip', 1);
		}
	}
	*/
	
	$per_page = 50000;
	$query = array();
	
	
	foreach (array('Region', 'POI') AS $type) {
		do {
			if(!$db->con->connected) {
				$db = connect_mongodb('tripfm');
			}
			$res = $db->db->$type->find($query, array('area', 'name', 'googleImages'))->sort(array('_id' => 1))->limit($per_page);
			$res = array_values(iterator_to_array($res));
			foreach ($res AS $item) {
				$query = array('_id' => array('$gt' => $item['_id']));
				if (!empty($item['googleImages'])) { continue; }
				$ip = $memcached->get('search_ip');
				$item['ip'] = long2ip($ip);
				$item['type'] = $type;
				gearman_client_dobackground($gearman_client, 'search_image', serialize($item));
				$memcached->increment('search_ip', 1);
			}
			$_SGLOBAL['logger']->info("$type _id: ".strval($item['_id'])." query");
			if (count($res) < $per_page) { break; }
		} while (1);
		$_SGLOBAL['logger']->info("$type query finish");
	}
	
	
	
	while (1) {
		$tmp_res = $gs->getStatus();
		$count = $tmp_res['operations']['search_image']['total'];
		if ($count > 0) {
			$_SGLOBAL['logger']->info("gearman queue count: $count"); 
			sleep(5); continue; 
		}
		exit;
	}
}

function google_search_image_download_listener() {
	ini_set('memory_limit', '512M');
	global $_SC, $_SGLOBAL;
	
	$memcached = new Memcached();
	$memcached->addServers($_SC['memcached_server']);
	
	_images_scan_exists_file(S_POI_IMG);
	
	$db = connect_mongodb('tripfm');
	
	$gearman_client = gearman_get_client();
	
	list($gearman_ip, $gearman_port) = explode(":", $_SC['gearman_server']);
	$gs = new GearmanServerStatus($gearman_ip, $gearman_port);
	
	while (1) {
		$tmp_res = $gs->getStatus();
		if (!isset($tmp_res['operations']['download_google_image']['total'])) { break; }
		$count = $tmp_res['operations']['download_google_image']['total'];
		if ($count > 0) {
			$_SGLOBAL['logger']->info("gearman queue count: $count"); 
			sleep(5); continue; 
		}
		break;
	}
	
	$per_page = 2000;
	$query = array();
	$prefix = "img_";
	
	foreach (array('Region', 'POI') AS $type) {
		do {
			if(!$db->con->connected) {
				$db = connect_mongodb('tripfm');
			}
			$res = $db->db->$type->find($query, array('name', 'googleImages'))->sort(array('_id' => 1))->limit($per_page);
			$res = array_values(iterator_to_array($res));
			foreach ($res AS $item) {
				$query = array('_id' => array('$gt' => $item['_id']));
				if (empty($item['googleImages'])) { continue; }
				foreach ($item['googleImages'] AS $googleImage) {
					$exists = $memcached->get($prefix.$googleImage['imageId']);
					if (!empty($exists)) { continue; }
					$googleImage['type'] = $type;
					gearman_client_dobackground($gearman_client, 'download_google_image', serialize($googleImage));
				}
			}
			$_SGLOBAL['logger']->info("$type _id: ".strval($item['_id'])." query");
			if (count($res) < $per_page) { break; }
		} while (1);
		$_SGLOBAL['logger']->info("$type query finish");
	}
	
	
	while (1) {
		$tmp_res = $gs->getStatus();
		$count = $tmp_res['operations']['download_google_image']['total'];
		if ($count > 0) {
			$_SGLOBAL['logger']->info("gearman queue count: $count"); 
			sleep(5); continue; 
		}
		exit;
	}
}

function google_search_image_download_listener_by_txt() {
	ini_set('memory_limit', '512M');
	global $_SC, $_SGLOBAL;
	
    if(!isset($_SGLOBAL['logger'])) {
		$_SC['log_in_shell'] = true;
		$_SGLOBAL['logger'] = Log::get_log_handle("a");
	}
	
	$memcached = new Memcached();
	$memcached->addServers($_SC['memcached_server']);
	
	_images_scan_exists_file(S_POI_IMG);
	
	$gearman_client = gearman_get_client();
	
	list($gearman_ip, $gearman_port) = explode(":", $_SC['gearman_server']);
	$gs = new GearmanServerStatus($gearman_ip, $gearman_port);
	
	while (1) {
		$tmp_res = $gs->getStatus();
		if (!isset($tmp_res['operations']['download_google_image']['total'])) { break; }
		$count = $tmp_res['operations']['download_google_image']['total'];
		if ($count > 0) {
			$_SGLOBAL['logger']->info("gearman queue count: $count"); 
			sleep(5); continue; 
		}
		break;
	}
	$fp = fopen("./undownloaded.txt", "r");
	
	$prefix = "img_";
	do {
	    $googleImage = unserialize(trim(fgets($fp)));
		$exists = $memcached->get($prefix.$googleImage['imageId']);
		if (!empty($exists)) { continue; }
		gearman_client_dobackground($gearman_client, 'download_google_image', serialize($googleImage));
	} while (!feof($fp));
	
	while (1) {
		$tmp_res = $gs->getStatus();
		$count = $tmp_res['operations']['download_google_image']['total'];
		if ($count > 0) {
			$_SGLOBAL['logger']->info("gearman queue count: $count"); 
			sleep(5); continue; 
		}
		exit;
	}
}

function article_image_download_listener_by_txt() {
	ini_set('memory_limit', '512M');
	global $_SC, $_SGLOBAL;
	
    if(!isset($_SGLOBAL['logger'])) {
		$_SC['log_in_shell'] = true;
		$_SGLOBAL['logger'] = Log::get_log_handle("a");
	}
	
	$gearman_func_name = "download_image";
	
	list($gearman_ip, $gearman_port) = explode(":", $_SC['gearman_server']);
	$gs = new GearmanServerStatus($gearman_ip, $gearman_port);
	
    while (1) {
		$tmp_res = $gs->getStatus();
		if (!isset($tmp_res['operations'][$gearman_func_name]['total'])) { break; }
		$count = $tmp_res['operations'][$gearman_func_name]['total'];
		if ($count > 0) {
			$_SGLOBAL['logger']->info("gearman queue count: $count"); 
			sleep(5); continue; 
		}
		break;
	}
	
	$memcached = new Memcached();
	$memcached->addServers($_SC['memcached_server']);
	$prefix = "img_";
	$per_page = 2000;
	
	_images_scan_exists_file("/sdb2/article", "/sdb2/articleOriginal3");
	//_images_scan_exists_file("/sdb2/article");
	
	$gearman_client = gearman_get_client();
	$db = connect_mongodb('page');
	$all_count = $exists_count = 0;
	$query_count = array();
    $query = array();
    /*
	do {
	    $downloaded_images_md5s = array();
		if(!$db->con->connected) {
			$db = connect_mongodb('page');
		}
		$res = $db->db->Images->find($query)->sort(array('_id' => 1))->limit($per_page);
		$res = array_values(iterator_to_array($res));
		foreach ($res AS $item) {
			$query = array('_id' => array('$gt' => $item['_id']));
	        $all_count ++;
			$exists = $memcached->get($prefix.$item['md5']);
			if (!empty($exists)) {
			    $exists_count++; 
			    $downloaded_images_md5s[] = $item['md5'];
			    continue; 
			}
			gearman_client_dobackground($gearman_client, 'download_image', serialize($item));
		}
		$sig = $db->db->Images->update(array("md5" => array('$in' => $downloaded_images_md5s)),
		                               array('$set' => array('status' => 200)),
		                               array("multiple" => true));
        isset($query_count[$sig]) ? $query_count[$sig]++ : $query_count[$sig] = 1;
		if (count($res) < $per_page) { break; }
	} while (1);
	*/
	$fp = fopen("./undownloaded.txt", "r");
	$downloaded_images_md5s = array();
	do {
	    $all_count ++;
	    $image = trim(fgets($fp));
	    $item = unserialize($image);
	    $exists = $memcached->get($prefix.$item['md5']);
		if (!empty($exists)) {
		    $exists_count++; 
		    $downloaded_images_md5s[] = $item['md5'];
		    continue; 
		}
		gearman_client_dobackground($gearman_client, $gearman_func_name, $image);
		if(count($downloaded_images_md5s) < 2000) { continue; }
		$sig = $db->db->Images->update(array("md5" => array('$in' => $downloaded_images_md5s)),
		                               array('$set' => array('status' => 200)),
		                               array("multiple" => true));
        $downloaded_images_md5s = array();
        isset($query_count[$sig]) ? $query_count[$sig]++ : $query_count[$sig] = 1;
	} while (!feof($fp));
	$_SGLOBAL['logger']->info("query_count:".json_encode($query_count));
	$_SGLOBAL['logger']->info("undownloaded images:".($all_count - $exists_count));
	$_SGLOBAL['logger']->info("query finish");
	
	while (1) {
		$tmp_res = $gs->getStatus();
		$count = $tmp_res['operations'][$gearman_func_name]['total'];
		if ($count > 0) {
			$_SGLOBAL['logger']->info("gearman queue count: $count"); 
			sleep(5); continue; 
		}
		exit;
	}
}

function _images_scan_exists_file() {
	global $_SC, $_SGLOBAL;
	$memcached = new Memcached();
	$memcached->addServers($_SC['memcached_server']);
	$prefix = "img_";
	
	clearstatcache();
	$exists = array();
	
	$count = 0;
	$mother_dirs = func_get_args();
	foreach ($mother_dirs AS $mother_dir) {
		if ($mother_dir == '.' || $mother_dir == '..') { continue; }
		$mother_dir = rtrim($mother_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		if (!is_dir($mother_dir)) { continue; }
		$files = scandir($mother_dir);
		foreach ($files as $file) {
			if ($file == '.' || $file == '..') { continue; }
			if (is_dir($mother_dir.$file)) {
				$child_files = scandir($mother_dir.$file);
				$count += count($child_files) - 2;
				foreach ($child_files as $child_file) {
					if ($child_file == '.' || $child_file == '..') { continue; }
					$mem_key = $prefix.$child_file;
					$memcached->set($mem_key, true);
				}
			} else {
			    $exists[$file] = true;
				$mem_key = $prefix.$file;
				$memcached->set($mem_key, true);
				$count ++;
			}
		}
		
	}
	if(!isset($_SGLOBAL['logger'])) {
		$_SC['log_in_shell'] = true;
		$_SGLOBAL['logger'] = Log::get_log_handle("a");
	}
	$_SGLOBAL['logger']->info("exists $count images, duplicate: ".count($exists));
	
}

function _images_scan_exists_file_count() {
	global $_SC, $_SGLOBAL;
	$count = 0;
	$mother_dirs = func_get_args();
	foreach ($mother_dirs AS $mother_dir) {
		if ($mother_dir == '.' || $mother_dir == '..') { continue; }
		$files = scandir($mother_dir);
		$mother_dir = rtrim($mother_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		foreach ($files as $file) {
			if ($file == '.' || $file == '..') { continue; }
			if (is_dir($mother_dir.$file)) {
				$child_files = scandir($mother_dir.$file);
				$count += count($child_files) - 2;
			} else {
				$count ++;
			}
		}
		
	}
	if(isset($_SGLOBAL['logger'])) {
		$_SGLOBAL['logger']->info("exists $count images");
	} else {
		$_SC['log_in_shell'] = true;
		$log = Log::get_log_handle("a");
		$log->info($count);
	}
}

function scan_stats() {
	$dirs = func_get_args();
	$stats = array();
	foreach ($dirs AS $dir) {
		_scan_stats($dir, $stats);
	}
	ksort($stats);
	echo "\n";
	foreach ($stats AS $threshold => $count) {
		$threshold = ($threshold < 1024) ? $threshold.'k' : intval($threshold / 1024).'m'; 
		printf("size level %4s: %-10d\n", $threshold, $count);
	}
	echo "total: ".array_sum($stats)."\n";
}

function _scan_stats($dir, &$stats) {
	$dir = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
	$files = scandir($dir);
	foreach ($files AS $file) {
		if ($file == '.' || $file == '..') { continue; }
		if (is_dir($dir.$file)) {
			_scan_stats($dir.$file, $stats);
			continue;
		}
		$file_stat = stat($dir.$file);
		$size = $file_stat['size'];
		$size_kb = intval($size / 1024);
		do {
			if ($size_kb < 1) {
				$size_threshold_kb = 1;
				break;
			}
			if ($size_kb > 32 * 1024) {
				$size_threshold_kb = 32 * 1024;
				break;
			}
			$size_threshold_kb = pow(2, intval(log($size_kb, 2))); 
		} while(0);
		isset($stats[$size_threshold_kb]) ? $stats[$size_threshold_kb]++
										  : $stats[$size_threshold_kb] = 1;
	}
}

function cp_image($dir, $destination_dir)
{
	$dir = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
	$destination_dir = rtrim($destination_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
	!is_dir($destination_dir) && mkdir($destination_dir, 0755, true);
	$files = scandir($dir);
	foreach ($files AS $file) {
		if ($file == '.' || $file == '..') { continue; }
		//list($md5) = explode(".", $file);
		//$sub_dir = $destination_dir.substr($md5, 0, 1);
		//!is_dir($sub_dir) && mkdir($sub_dir, 0755, true);
		//$sub_dir .= DIRECTORY_SEPARATOR;
		
		$image_stat = getimagesize($dir.$file);
		if($image_stat === false) { 
			unlink($dir.$file); 
			continue; 
		}
		list(, $type) = explode('/', $image_stat['mime']);
		copy($dir.$file, $destination_dir."$file.$type");
	}
}

function filter_images()
{
    global $_SC, $_SGLOBAL;
	$_SC['log_in_shell'] = true;
	$_SGLOBAL['logger'] = Log::get_log_handle("a");
	
	if (func_num_args() < 2) { die("destination dir is missing\n"); }
	$dirs = func_get_args();
	$destination_dir = array_pop($dirs);
	$destination_dir = rtrim($destination_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
	!is_dir($destination_dir) && mkdir($destination_dir, 0755, true);
	foreach ($dirs AS $dir) {
		_filter_scan_images($dir, $destination_dir);
	}
}

function _filter_scan_images($dir, $destination_dir)
{
    global $_SGLOBAL;
	$dir = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
	$files = scandir($dir);
	$count = 0;
	foreach ($files AS $file) {
		if ($file == '.' || $file == '..') { continue; }
		if (is_dir($dir.$file)) {
			_filter_scan_images($dir.$file, $destination_dir);
			continue;
		}
		$file_stat = stat($dir.$file);
		$size = $file_stat['size'];
		$size_kb = intval($size / 1024);
		if ($size_kb < 16) { continue; }
		$image_stat = getimagesize($dir.$file);
		if($image_stat === false) { continue; }
		list($width, $height) = $image_stat;
		if($width * $height < 37500) { continue; }
		if (is_file($destination_dir.$file)) { continue; }
		copy($dir.$file, $destination_dir.strtolower($file));
		$count ++;
	}
	$_SGLOBAL['logger']->info("Count: ".$count);
}


function dump_none_downloaded_googleImages() {
    ini_set('memory_limit', '512M');
	global $_SC, $_SGLOBAL;
	$_SC['log_in_shell'] = true;
	$_SGLOBAL['logger'] = Log::get_log_handle("a");
	$memcached = new Memcached();
	$memcached->addServers($_SC['memcached_server']);
	
	_images_scan_exists_file(S_POI_IMG);
    $db = connect_mongodb('tripfm');
    
    $per_page = 2000;
	$prefix = "img_";
	$fp = fopen("/home/hourui/img.downloader/undownloaded.txt", "w+");
    foreach (array('Region', 'POI') AS $type) {
        $all_count = $exists_count = 0;
        $query = array();
		do {
			if(!$db->con->connected) {
				$db = connect_mongodb('tripfm');
			}
			$res = $db->db->$type->find($query, array('name', 'googleImages'))->sort(array('_id' => 1))->limit($per_page);
			$res = array_values(iterator_to_array($res));
			foreach ($res AS $item) {
				$query = array('_id' => array('$gt' => $item['_id']));
				if (empty($item['googleImages'])) { continue; }
			    foreach ($item['googleImages'] AS $googleImage) {
			        $googleImage['collection'] = $type;
			        $all_count ++;
					$exists = $memcached->get($prefix.$googleImage['imageId']);
					if (!empty($exists)) {
					    $exists_count++; 
					    continue; 
					}
					fwrite($fp, serialize($googleImage)."\n");
				}
			}
			$_SGLOBAL['logger']->info("$type _id: ".strval($item['_id'])." query");
			if (count($res) < $per_page) { break; }
		} while (1);
		var_dump($exists_count, $all_count);
		$_SGLOBAL['logger']->info("$type query finish");
	}
}

function dump_none_downloaded_articleImages() {
    ini_set('memory_limit', '512M');
	global $_SC, $_SGLOBAL;
	$_SC['log_in_shell'] = true;
	$_SGLOBAL['logger'] = Log::get_log_handle("a");
//	$memcached = new Memcached();
//	$memcached->addServers($_SC['memcached_server']);
	
	//_images_scan_exists_file("/sdb2/article");
    $db = connect_mongodb('page');
    
    $per_page = 2000;
	$prefix = "img_";
	$fp = fopen("/home/hourui/img.downloader/undownloaded.txt", "w+");
    $all_count = $exists_count = 0;
    $query = array('status' => 999);
	do {
	    $downloaded_images_ids = array();
		if(!$db->con->connected) {
			$db = connect_mongodb('page');
		}
		$res = $db->db->Images->find($query)->sort(array('_id' => -1))->limit($per_page);
		$res = array_values(iterator_to_array($res));
		foreach ($res AS $item) {
			$query['_id'] = array('$lt' => $item['_id']);
	        $all_count ++;
//			$exists = $memcached->get($prefix.$item['md5']);
//			if (!empty($exists)) {
//			    $exists_count++; 
//			    $downloaded_images_ids[] = $item['md5'];
//			    continue; 
//			}
			fwrite($fp, serialize($item)."\n");
		}
//		$sig = $db->db->Images->update(array("md5" => array('$in' => $downloaded_images_ids)),
//		                               array('$set' => array('status' => 100)),
//		                               array("multiple" => true));
		$_SGLOBAL['logger']->info("_id: ".strval($item['_id'])." query");
		if (count($res) < $per_page) { break; }
	} while (1);
	var_dump($all_count);
	$_SGLOBAL['logger']->info("query finish");
}

function article_update_images_status($article_images_dir)
{
    ini_set('memory_limit', '512M');
    $article_images_dir = rtrim($article_images_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    $db = connect_mongodb('page');
    $status = $db->db->Images->update(
                            array('status' => array('$ne' => 999)),
		                    array('$set' => array('status' => 0)),
		                    array("multiple" => true,
		                          "safe"     => true,
		                          "timeout"  => 2 * 60 * 1000) // 2s
		                    );
    var_dump($status);
    $files = scandir($article_images_dir);
    $images_md5s = array();
    $all_status = array();
    foreach ($files AS $file) {
        if (in_array($file, array('.', '..'))) { continue; }
        $images_md5s[] = $file;
        if(count($images_md5s) >= 1000) {
            $status = $db->db->Images->update(
                                    array("md5" => array('$in' => $images_md5s)),
	                                array('$set' => array('status' => 100)),
	                                array("multiple" => true, "safe" => true, "timeout"  => 2 * 60 * 1000));
            isset($all_status[$status['ok']]) ? $all_status[$status['ok']] += $status['n'] :
                                                $all_status[$status['ok']] =  $status['n'];
            $images_md5s = array();
        }
    }
    if(!empty($images_md5s)) {
        $status = $db->db->Images->update(
                              array("md5" => array('$in' => $images_md5s)),
                              array('$set' => array('status' => 100)),
                              array("multiple" => true, "safe" => true, "timeout"  => 2 * 60 * 1000));
        isset($all_status[$status['ok']]) ? $all_status[$status['ok']] += $status['n'] :
                                            $all_status[$status['ok']] =  $status['n'];
        $images_md5s = array();
    }
    var_dump($all_status);
}
