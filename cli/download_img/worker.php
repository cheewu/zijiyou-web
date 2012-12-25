<?php
require_once 'common.php';

count($argv) != 2 && die("Usage: php worker.php (start|stop|status)\n");

$worker = new WorkerExtractArticleImage($_SC['gearman_server'], 2, S_PID);

$main_pid = S_PID."main_process.pid";

switch ($argv[1]) {
	case 'start':
		Job::start($main_pid, S_PID, $_SCONFIG['gearman_worker_options'], $_SCONFIG['listener_functions']);
	break;
	case 'stop':
		Job::stop($main_pid, S_PID);
	break;
	case 'status':
		Job::status($main_pid, S_PID);
	break;
	default: die("Usage: php worker.php (start|stop|status)\n");
}