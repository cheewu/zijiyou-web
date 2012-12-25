<?php
if(!defined('IN_SYSTEM')) {	exit('Access Denied'); }
/**
 * log
 * 
 * @author HouRui
 * @since 2012-04
 */
class Log {
	
	/**
	 * log file name
	 * will add the split part of filename 
	 * @var string
	 */
	private $filename = '';
	
	/**
	 * log folder
	 * will add the split part of folder
	 * @var string
	 */
	private $dirname = '';
	
	/**
	 * base filename
	 * @var string
	 */
	private $base_filename = '';
	
	/**
	 * base dirname
	 * @var string
	 */
	private $base_dirname = '';
	
	/**
	 * date fotmat
	 * @var string
	 */
	private $date_format = 'Y-m-d H:i:s';
	
	/**
	 * log message
	 * @var string
	 */
	private $message = '';
	
	/**
	 * log level
	 * @var string (info|notice|error|pid)
	 */
	private $level;
	
	/**
	 * log suffix
	 * @var string
	 */
	private $suffix = 'log';
	
	/**
	 * split method
	 * @var array(
	 * 	'm' => bool, split folder per month
	 * 	'd' => bool, split file per day
	 * )
	 */
	private $split = array();
	
	/**
	 * debug handle the log out by level
	 * @var int
	 * 0 for nothing
	 * 1 for pid & error & notice & info
	 * 2 for pid & error & notice
	 * 3 for pid & error
	 */
	private $debug = 1;
	
	/**
	 * tracecode
	 * @var bool
	 */
	private $backtrace = false;
	
	/**
	 * echo log instead of write log file 
	 * @var bool
	 */
	private $echo_log = false;
	
	/**
	 * single log content
	 * @var string
	 */
	private $single_log_content = "";
	
	/**
	 * is cache
	 * @var bool
	 */
	public $is_cache = false;
	
	/**
	 * cache size
	 * @var int
	 */
	private $cache_size = 500;
	
	/**
	 * log cache
	 * @var array
	 */
	public $cache = array();
	
	/**
	 * wite log to specific var
	 * @var array
	 */
	public $log_var = null;
	
	/**
	 * switch of write log to specific var
	 * @var bool
	 */
	private $is_write_log_to_specific_var = false;
	
	/**
	 * __construct
	 * @param string $log_filename
	 * @param string $log_dirname
	 * @param array() $log_options
	 */
	public function __construct($log_filename = '', $log_dirname = '', $log_options = array())
	{
		global $_SC;
		$default_log_options = array(
			'debug'            => 1,
			'cache_size'       => 500,
			'backtrace'        => false,
			'echo_log'         => false,
			'folder_per_month' => true,
			'file_per_day'     => true,
		);
		$log_options = array_merge($default_log_options, $log_options);
		$this->split['m'] = $log_options['folder_per_month'];
		$this->split['d'] = $log_options['file_per_day'];
		$this->debug      = $log_options['debug'];
		$this->cache_size = $log_options['cache_size'];
		$this->backtrace  = $log_options['backtrace'];
		$this->echo_log   = $log_options['echo_log'];
		$this->base_dirname  = rtrim($log_dirname, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		$this->base_filename = trim($log_filename);
		empty($this->base_dirname)  && die("log dirname is empty\n");
		empty($this->base_filename) && die("log filename is empty\n");
	}
	
	/**
	 * create the folder or file if not exists
	 */
	private function init_file()
	{
		clearstatcache();
		$this->dirname  = $this->base_dirname;
		$this->filename = $this->base_filename;
		$this->split['m'] && $this->dirname  .= date("Y-m").DIRECTORY_SEPARATOR;
		$this->split['d'] && $this->filename .= date("_Y-m-d");
		$this->filename .= '.'.$this->suffix;
		!is_dir($this->dirname) && mkdir($this->dirname, 0755, true);
		!file_exists($this->dirname.$this->filename) && touch($this->dirname.$this->filename);
	}
	
	/**
	 * write log
	 */
	private function write()
	{
		$this->format_log();
		if($this->is_cache) {
			$this->cache_in();
			return true;
		}
		return $this->record_log();
	}
	
	/**
	 * write log
	 * @return bool
	 */
	private function format_log()
	{
		$level_pad = str_pad("[{$this->level}]", 9, " ", STR_PAD_RIGHT);
		$this->single_log_content = date($this->date_format)." $level_pad".$this->message;
		if($this->backtrace) {
			$backtrace = array();
			$backtrace[] = "ERROR backtrace:";
			$trace = debug_backtrace();
			foreach($trace AS $value){
				$backtrace[] = $value['file'].'; line: '.$value['line'];
			}
			$this->single_log_content = "\n".implode("\n", $backtrace)."\n".$this->single_log_content;
		}
	}
	
	/**
	 * record log
	 * if $this->echo_log is true, it will put log in output
	 * if $this->is_write_log_to_specific_var, it will put log in $this->log_var
	 * otherwise
	 * it will init log file dir and file
	 * then write log
	 */
	private function record_log()
	{
		// echo log
		if($this->echo_log){
			echo $this->single_log_content."\n";
			return true;
		}
		// write log to specific var
		if($this->is_write_log_to_specific_var) {
			$this->log_var[] = $this->single_log_content;
			return true; 
		}
		$this->init_file();
		$fp = fopen($this->dirname.$this->filename, 'ab');
		if($fp === false) {return false;}
		$res = fwrite($fp, $this->single_log_content."\n");
		fclose($fp);
		unset($fp);
		return $res !== false;
	}
	
	/**
	 * cache log
	 * @param array $content
	 *     array(
	 *        'level'   => (pid|error|notice|info),
	 *        'message' => 'xxx'
	 *     )
	 */
	private function cache_in(){
		$this->cache[] = $this->single_log_content;
		if(count($this->cache) > $this->cache_size) {
			$this->single_log_content = array_shift($this->cache);
			$this->record_log();
		}
	}
	
	/************************* public ********************************/
	/**
	 * magic function
	 * @param string $method
	 * @param array $params
	 */
	public function __call($method, $params) {
		if( preg_match_all("/^(info|notice|error|pid)$/i", $method, $matches) ) {
			/**
			 * debug handle the log out by level
			 * @var int
			 * 0 for nothing
			 * 1 for pid & error
			 * 2 for pid & error & notice
			 * 3 for pid & error & notice & info
			 */
			if($this->debug <= 0) {return;} 
			$this->level = strtoupper($matches[1][0]);
			$this->message = trim($params[0]);
			switch ($this->level) {
				case 'INFO':
					if($this->debug < 3) return;
					break;
				case 'NOTICE':
					if($this->debug < 2) return;
					break;
				case 'ERROR':
					if($this->debug < 1) return;
					break;
				default: break;
			}
			return $this->write();
		}
		/* error */
		die("method [$method] doesn't exists in class [".get_class()."]\n");
	}
	
	/**
	 * cache open
	 */
	public function cache_start() 
	{
		$this->is_cache = true;
	}
	
	/**
	 * end and out put cache
	 */
	public function cache_end_flush()
	{
		$this->is_cache = false;
		foreach ($this->cache AS $this->single_log_content) {
			$this->record_log();
		}
	}
	
	/**
	 * write log to specific var
	 * @param array $var
	 */
	public function write_log_to_var(&$var) 
	{
		$this->log_var = &$var;
		$this->is_write_log_to_specific_var = true;
	}
	
	/**
	 * get log handle
	 * @param string $log_filename
	 */
	public static function get_log_handle($log_filename)
	{
		global $_SC;
		$log_options = array(
			'debug'            => isset($_SC['log_level'])                  ? $_SC['log_level']                  : 1,
			'backtrace'        => isset($_SC['log_func_backtrace'])         ? $_SC['log_func_backtrace']         : false,
			'echo_log'         => isset($_SC['log_in_shell'])               ? $_SC['log_in_shell']               : false,
			'folder_per_month' => isset($_SC['log_split_folder_per_month']) ? $_SC['log_split_folder_per_month'] : true,
			'file_per_day'     => isset($_SC['log_split_file_per_day'])     ? $_SC['log_split_file_per_day']     : false,
		);
		$log_dirname = defined('S_LOG') ? S_LOG : dirname(__FILE__);
		return new Log($log_filename, $log_dirname, $log_options);
	}
}