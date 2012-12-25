<?php

abstract class BaseGearmanWorker {

	/**
	 * @var $german_server
	 */
	public $gearman_servers; 
	
	/**
	 * gearman worker obj
	 * @var obj
	 */
	public $gearman_worker;
	
	/**
	 * gearman client
	 * @var obj
	 */
	public $gearman_client;
	
	/**
	 * gearman 注册函数名
	 * @var string
	 */
	public $gearman_register_func_name;
	
	/**
	 * logger
	 * @var object
	 */
	public $logger;
	
	/**
	 * self handle
	 * @var object
	 */
	public static $handle = null;
	
	/**
	 * 构造函数
	 * @param string $servers
	 */
	public function __construct($servers) {
		$this->german_servers = $servers; //gearman server
		$this->gearman_init();
		$this->logger = Log::get_log_handle($this->gearman_register_func_name);
		$this->_worker_loop_init();
	}
	
	/**
	 * 初始化gearman参数
	 * 必须初始化 $this->gearman_register_func_name 注册函数名
	 */
	public abstract function gearman_init();
	
	/**
	 * 初始化每个子进程参数
	 */
	public function worker_init() {}
	
	/**
	 * init worker loop
	 */
	public function _worker_loop_init() {
		$this->worker_init();
		/* register worker */
		$this->gearman_worker = new GearmanWorker();
		$this->gearman_worker->addServers($this->german_servers);
		$this->gearman_worker->addFunction($this->gearman_register_func_name/*register*/, array($this, "worker_func")/*do*/);
		/* init a client */
		$this->gearman_client = new GearmanClient();
		$this->gearman_client->addServers($this->german_servers);
	}
	/**
	 * 任务启动
	 */
	public function worker_loop() {
		while(1) {
			// init error log
			$this->error_info = '';
			/*
			 * GearmanWorker::work
			 * Waits for a job to be assigned and then calls the appropriate callback function. 
			 * Issues an E_WARNING with the last Gearman error if the return code is not one of GEARMAN_SUCCESS, GEARMAN_IO_WAIT, or GEARMAN_WORK_FAIL.
			 */
			if(@$this->gearman_worker->work() === true) { continue; }
			
			$return_code = $this->gearman_worker->returnCode();
			
			if($return_code == GEARMAN_SUCCESS) { continue; }
			
			sleep(1);// 出错sleep1秒
		}
	}
	
	/**
	 * 执行函数
	 */
	public abstract function worker_func($job);
}


