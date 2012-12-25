<?php
/**
 * Worker
 * post sina weibo
 * 
 * @author HouRui
 * @since 2012-01
 * 
 */
class WorkerExtractArticleImage extends BaseGearmanWorker {
	
	/**
	 * mongodb handle
	 * @var object
	 */
	public $mongodb;
	
	// init gearman param
	public function gearman_init() {
		global $_SCONFIG;
		$this->gearman_register_func_name = "extract_article_image";
	}
	
	// init worker param
	public function worker_init() {
		$this->_connect_mongodb();
	}
	
	// worker func
	public function worker_func($job) {
		global $_SCONFIG;
	
		if(!$this->mongodb->con->connected) {
			$this->_connect_mongodb();
		}
		
		//unserialize $paramArr
		$article_id = $job->workload();
		
		$article = $this->mongodb->Article_select_one(array('_id' => new MongoID($article_id)), array('content', 'url', 'is_extract_image'));
		
		if($article['is_extract_image']) { return; }
		preg_replace_callback("#<\s*img.*?real_src\s*=\s*[\"']([^\"]*)[\"'].*?/\s*>#", 
		function($matches) {return "<img src='$matches[1]' />";}, 
		$article['content']);
		
		preg_match_all("#<\s*img.*?src\s*=\s*[\"']([^\"]*)[\"'].*?/\s*>#", $article['content'], $matches);
		
		$images = $matches[1];
		
		$image_count = count($images);
		
		$this->mongodb->Article_update(array('image_count' => $image_count), array('_id' => new MongoID($article_id)));
		
		foreach ($images AS $image) {
			$image = trim($image);
			$md5 = md5($image);
			$is_exists = $this->mongodb->Images_select_one(array('md5' => $md5));
			if (!empty($is_exists)) { continue; } 
			preg_match_all("#(https?://)?([^/]*)/#", $image, $matches);
			if (empty($matches[2][0])) {
				preg_match_all("#(https?://)?([^/]*)/#", $article['url'], $matches);
				$image = $matches[2][0].$image;
			}
			
			$res = $this->mongodb->Images_insert(array('url'    => $image, 
			                                           'domain' => $matches[2][0], 
			                                           'refer'  => $article['url'], 
			                                           'md5'    => $md5, 
			                                           'status' => 0));
		}
		
		return;
	}
	
	/**
	 * connect to mongodb
	 */
	function _connect_mongodb() {
		global $_SC;
		do{
			try {
				$this->mongodb = new MongoHandle($_SC['MongoDB']['server'], $_SC['MongoDB']['dbname'], $_SC['MongoDB']['options']);
			} catch (MongoConnectionException $e) {
				$this->loger->error('Mongodb connect error:'.$e->getMessage().' sleep 5');
				sleep(5);
				continue;
			}
			return;
		} while(1);
	}
	
}
