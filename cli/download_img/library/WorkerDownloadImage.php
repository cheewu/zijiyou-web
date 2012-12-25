<?php
/**
 * Worker
 * post sina weibo
 * 
 * @author HouRui
 * @since 2012-01
 * 
 */
class WorkerDownloadImage extends BaseGearmanWorker {
	
	/**
	 * mongodb handle
	 * @var object
	 */
	public $mongodb;
	
	/**
	 * image url
	 * @var string
	 */
	public $url;
	
	/**
	 * image refer
	 * @var string
	 */
	public $refer;
	
	/**
	 * domain
	 * @var string
	 */
	public $domain;
	
	/**
	 * memcached
	 * @var object
	 */
	public $memcached;
	
	/**
	 * error
	 * @var string
	 */
	public $error;
	
	// init gearman param
	public function gearman_init() {
		global $_SCONFIG;
		$this->gearman_register_func_name = "download_image";
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
		$this->_memcached_init();
		
		//unserialize $paramArr
		$work_load = $job->workload();
		
		$image = unserialize($work_load);
		
		if (empty($image['domain'])) {
			if(preg_match_all("#(https?://)?([^/]*)/#", $image['refer'], $matches)) {
				$image['domain'] = $matches[2][0];
				$image['url']    = $matches[1][0].$image['domain'].$image['url'];
			}
		}
		
		$this->url    = $image['url'];
		$this->refer  = $image['refer'];
		//echo "get work_load\n";
		$frequency_memcached_key = 'zjy_domain_'.md5($image['domain']);
		$exists_memcached_key    = 'img_'.$image['md5'];
		$dirname = "/sdb2/articleOriginal3/";

		clearstatcache();
		!is_dir($dirname) && mkdir($dirname, 0755, true);
		$filename = $dirname.$image['md5'];
		
		if( is_file($filename) ) { return; }
		
		$original_timestamp = $this->memcached->get($frequency_memcached_key);
		if($original_timestamp !== false && time() - $original_timestamp < 3) {
			//return $this->logger->error($image['domain']." is out of frequency");
			return;
		}
		//echo "start download\n";
		//echo $this->url."\n";
		$res = $this->_download_image();
		//echo "finish download\n";
		if(empty($res)) { return; }
		
		//echo "start put file\n";
		if (!file_put_contents($filename, $res)) {
			return $this->logger->error($this->url.' write file error');
		}
		$this->memcached->set($frequency_memcached_key, time());
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
	
	/**
	 * download image
	 */
	public function _download_image() {
	    $ch = curl_init();
	    //url
	    curl_setopt($ch, CURLOPT_URL, $this->url);
	    //instead of outputting it out directly
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    //automatically set the Referer
	    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	    //TRUE to follow any "Location: " header that the server sends
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,  true);    
	    //maximum amount of HTTP redirections to follow
	    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
	    //The number of seconds to wait whilst trying to connect. Use 0 to wait indefinitely
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	    //set the maximum seconds to download image
	    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	    //Set User-agent
	    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)');
	    //TRUE to include the header in the output
	    curl_setopt($ch, CURLOPT_HEADER, false);
	    //Add refer to get pictures
	    !empty($this->refer) && curl_setopt($ch, CURLOPT_REFERER, $this->refer); 
	    //HTTPS
	    if( stripos($this->url, "https://") !== FALSE ) {
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    }
	    $data = curl_exec($ch);
	    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    do {
            if (curl_errno($ch) > 0) {
            	$this->logger->error($this->url." download error\n".curl_error($ch));
            	$data = false;
            	break;
            }
    	    if ($http_status != 200) {
            	$this->logger->error($this->url." http response: $http_status");
            	$data = false;
            	break;
            }
	    } while (0);
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
