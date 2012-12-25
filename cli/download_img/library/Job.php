<?php
if(!defined('IN_SYSTEM')) {	exit('Access Denied'); }
/**
 * handle Job class
 * @author HouRui
 * @since 2012-05
 */
class Job {
	
	/**
	 * main process name
	 * pid & log file will use this name
	 * @var string
	 */
	private static $main_process_name;
	
	/**
	 * pid dirname
	 * @var string
	 */
	private static $pid_dir;
	
	/**
	 * summary pid filename
	 * @var string
	 */
	private static $summary_pid_filename = "summary.pids";
	
	/**
	 * logger
	 * @var object
	 */
	private static $logger;
	
	/**
	 * start entire system
	 * @param string $main_pid_file
	 * @param string $pid_dir
	 */
	public static function start($main_pid_file, $pid_dir, $gearman_options, $listener_funcs) 
	{
		global $_SGLOBAL;
		Process::run_as_deamon($main_pid_file);
		self::$main_process_name = basename($main_pid_file, '.pid');
		self::$logger = Log::get_log_handle(self::$main_process_name);
		self::$logger->pid(self::$main_process_name.' start');
		self::$pid_dir = rtrim($pid_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		self::start_gearman_workers($gearman_options);
		self::start_listener($listener_funcs);
		/* listen all child process's exit */
		self::listen_child_process();
	}
	
	/**
	 * stop all workers by pid file 
	 */
	public static function stop($main_pid_file, $pid_dir)
	{
		global $_SGLOBAL;
		self::$main_process_name = basename($main_pid_file, '.pid');
		self::$pid_dir = rtrim($pid_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		!is_file(self::$pid_dir.self::$summary_pid_filename) && die("system is not running\n");
		Process::kill_by_pidfile($main_pid_file);
		$logger = Log::get_log_handle(self::$main_process_name);
		$logger->pid(self::$main_process_name.' stop');
		$pids = unserialize(file_get_contents(self::$pid_dir.self::$summary_pid_filename));
		foreach($pids AS $pid => $pid_process_param) {
			Process::kill_by_pidfile($pid_process_param[1]);
		}
		unlink(self::$pid_dir.self::$summary_pid_filename);
	}
	
	/**
	 * get system status
	 * @param string $main_pid_file
	 * @param string $pid_dir
	 */
	public static function status($main_pid_file, $pid_dir)
	{
		self::$main_process_name = basename($main_pid_file, '.pid');
		self::$pid_dir = rtrim($pid_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		!is_file(self::$pid_dir.self::$summary_pid_filename) && die("system is not running\n");
		$pids = unserialize(file_get_contents(self::$pid_dir.self::$summary_pid_filename));
		$pid_status_default = array('alive' => 0, 'dead' => array());
		$res = array();
		foreach($pids AS $pid => $pid_process_param) {
			!isset($res[$pid_process_param[2]][$pid_process_param[0]]) && 
			       $res[$pid_process_param[2]][$pid_process_param[0]] = $pid_status_default;
			$tmp_status = &$res[$pid_process_param[2]][$pid_process_param[0]];
			if(Process::is_alive($pid)) {
				$tmp_status['alive']++;
			} else {
				$tmp_status['dead'][] = basename($pid_process_param[1], '.pid');
			}
		}
		unset($tmp_status);
		var_dump($res);
	}
	
	
	/**
	 * Enter description here ...
	 * @param array $gearman_options
	 * @example array('$GEARMAN_CLASS' => [int worker_count])
	 */
	private static function start_gearman_workers($gearman_options) 
	{
		foreach($gearman_options AS $gearman_class => $worker_count) {
			for ($worker_id = 0; $worker_id < $worker_count; $worker_id++) {
				$pid_file = self::$pid_dir.$gearman_class.'_'.$worker_id.'.pid';
				self::_start_job_process($gearman_class, $pid_file, 'gearman');
			}
		}
	}
	
	/**
	 * start listener
	 * @param string $listener_funcs
	 */
	private static function start_listener($listener_funcs)
	{
		foreach($listener_funcs AS $func) {
			$pid_file = self::$pid_dir.$func.'.pid';
			self::_start_job_process($func, $pid_file, 'listener');
		}
	}
	
	/**
	 * if child process exit father will restart
	 */
	public static function listen_child_process() 
	{
		global $_SGLOBAL;
		// loop
		while(1) {
			$death_child_pid = pcntl_wait($wait);
			$process_param = $_SGLOBAL['job_process'][$death_child_pid];
			self::$logger->pid("job: ".basename($process_param[1], '.pid').", exit signal $wait");
			sleep(10); /* sleep 10 seconds and restart worker */
			$process_param[3] = true;
			unset($_SGLOBAL['job_process'][$death_child_pid]);
			call_user_func_array('Job::_start_job_process', $process_param);
			self::$logger->pid("job: ".basename($process_param[1], '.pid').", restart");
		}
	}
	
	/**
	 * gearman start
	 * @param unknown_type $gearman_handle
	 * @param unknown_type $worker_name
	 * @param unknown_type $pid_file
	 * @param unknown_type $is_restart
	 */
	private static function _start_job_process($name, $pid_file, $type, $is_restart = false)
	{
		global $_SC, $_SGLOBAL;
		/**
		 * father get child's pid
		 * child get 0
		 * error get -1
		 * @see http://cn2.php.net/manual/en/function.pcntl-fork.php
		 */
		$pid_fork = pcntl_fork();
		$pid_fork == -1 && die("fork error\n");
		/**
		 * handle father process
		 * put the process info in the $_SGLOBAL['job_process']
		 */
		if($pid_fork != 0) {
			/********     father action       ********/
			$_SGLOBAL['job_process'][$pid_fork] = array($name, $pid_file, $type);
			// record all pids to a pid file
			file_put_contents(self::$pid_dir.self::$summary_pid_filename, serialize($_SGLOBAL['job_process']));
			return;
			/********    father action end    ********/
		} 
		
		/***********************************************************************\
		|                    below is child process action                      |
		\***********************************************************************/
		empty($pid_file) && die("process must have a pid file, pid file must not be empty\n"); 
		clearstatcache();
		/*
		 * if pid file exists 
		 * check if the process is alive 
		 * if process is alive
		 * kill it anyway
		 */ 
		if(is_file($pid_file)) {
			// get the pid number
			$pid = file_get_contents($pid_file);
			// kill the worker process if exists
			!empty($pid) && Process::is_alive($pid) && Process::kill($pid, 9, !$is_restart);
		} else { 
			// if file not exists create the file
			Process::recursive_touch($pid_file);
		}
		// write pid to pid file
		file_put_contents($pid_file, posix_getpid());
		/* job start */
		if($type == 'gearman') {
			$job = new $name($_SC['gearman_server']);
			$worker_name = basename($pid_file, '.pid');
			$job->logger->pid($worker_name.($is_restart ? ' restart' : ' start'));
			$job->worker_loop();
		} else {
			$_SGLOBAL['logger'] = Log::get_log_handle($name);
			$name();
		}
	}
}
