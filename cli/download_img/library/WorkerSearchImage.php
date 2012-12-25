<?php
/**
 * Worker
 * post sina weibo
 * 
 * @author HouRui
 * @since 2012-01
 * 
 */
class WorkerSearchImage extends BaseGearmanWorker {
	
	/**
	 * mongodb handle
	 * @var object
	 */
	public $mongodb;
	
	/**
	 * image url
	 * @var string
	 */
	public $base_url = "https://ajax.googleapis.com/ajax/services/search/images";
	
	
	// init gearman param
	public function gearman_init() {
		global $_SCONFIG;
		$this->gearman_register_func_name = "search_image";
	}
	
	// init worker param
	public function worker_init() {
		$this->_connect_mongodb();
		$this->_memcached_init();
	}
	
	// worker func
	public function worker_func($job) {
		global $_SCONFIG;
	
		if(!$this->mongodb->con->connected) {
			$this->_connect_mongodb();
		}
		
		$work_load = $job->workload();
		$item = unserialize($work_load);
		
		if (empty($item['name'])) { return; }
		
		$query_words = preg_replace("#[:：]#", "+", $item['name']);
		
		if ($item['type'] == 'Region' && !empty($item['area'])) {
			$query_words = $item['area'].'+'.$query_words;
		}
		
		$query = http_build_query(array('v' => '1.0', 'q' => $query_words, 'userip' => $item['ip']));
		$url = $this->base_url.'?'.$query;
		
		sleep(1);
		
		$res_json = $this->query_api($url);
		$res_arr  = json_decode($res_json, true);
		
		if (isset($res_arr['responseStatus']) && $res_arr['responseStatus'] == 200) {
			if (empty($res_arr['responseData']['results'])) {
				$this->logger->error("name:".$item['name']." url:".$url." is empty");
				return;
			}
			$func = $item['type'].'_update';
			$this->mongodb->$func(array('googleImages' => $res_arr['responseData']['results']), array('_id' => $item['_id']));
			return;
			
		}
		$this->logger->error("name:".$item['name']." url:".$url." is empty");
		$this->logger->error($res_arr['responseDetails']);
		sleep(120);
		
		
	}
	
	/**
	 * connect to mongodb
	 */
	function _connect_mongodb() {
		global $_SC;
		do{
			try {
				$this->mongodb = new MongoHandle($_SC['MongoDB']['server'], 'tripfm', $_SC['MongoDB']['options']);
			} catch (MongoConnectionException $e) {
				$this->loger->error('Mongodb connect error:'.$e->getMessage().' sleep 5');
				sleep(5);
				continue;
			}
			return;
		} while(1);
	}
	
	/**
	 * download image
	 */
	public function query_api($url) {
	    $ch = curl_init();
	    //url
	    curl_setopt($ch, CURLOPT_URL, $url);
	    //instead of outputting it out directly
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    //automatically set the Referer
	    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	    //TRUE to follow any "Location: " header that the server sends
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,  true);    
	    //maximum amount of HTTP redirections to follow
	    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
	    //The number of seconds to wait whilst trying to connect. Use 0 to wait indefinitely
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	    //set the maximum seconds to download image
	    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	    //Set User-agent
	    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)');
	    //TRUE to include the header in the output
	    curl_setopt($ch, CURLOPT_HEADER, false);
	    //Add refer to get pictures
	    //HTTPS
	    if( stripos($url, "https://") !== FALSE ) {
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    }
	    $data = curl_exec($ch);
        if (curl_errno($ch) > 0) {
        	$this->logger->error($this->url." download error\n".curl_error($ch));
        	$data = false;
        }
	    curl_close($ch);
	    return $data;
	} 
	
	/**
	 * conncect to memecached
	 */
	public function _memcached_init() {
		global $_SC;
		do {
			if( empty($this->memcached) ) { break; }
			$versions = $this->memcached->getVersion();
			if( empty($versions) ) { break; }
			return;
		} while(0);
		$this->memcached = new Memcached();
		$this->memcached->addServers($_SC['memcached_server']);
	}
	
	/**
	 * insert memcached
	 * @param string $domain
	 * @return int $original_timestamp
	 */
	public function get_and_set_memcached($memcached_key) {
		$timestamp = time();
		do {
			$original_timestamp = $this->memcached->get($memcached_key, null, $cas);
			if ($this->memcached->getResultCode() == Memcached::RES_NOTFOUND) {
		        $this->memcached->add($memcached_key, $timestamp);
		    } else { 
	        	$this->memcached->cas($cas, $memcached_key, $timestamp);
		    }
		    $this->_memcached_init();
		    usleep(100000);
		} while ($this->memcached->getResultCode() != Memcached::RES_SUCCESS);
	}
}
