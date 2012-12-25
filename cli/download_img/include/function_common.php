<?php
/**
 * Function - Common
 * 
 * @author HouRui
 * @since 2011-09
 */
if(!defined('IN_SYSTEM')) {	exit('Access Denied'); }

/**
 * extension loaded check
 * @param mix string/array $extensions
 */
function extension_check($extensions) {
	!is_array($extensions) && $extensions = array($extensions);
	$error = array();
	foreach($extensions AS $extension) {
		!extension_loaded($extension) && $error[] = $extension." is missing\n";
	} 
	!empty($error) && exit(implode("", $error));
}

/**
 * is cli usage
 * @return bool
 */
function is_cli() {
    return strtolower(substr(php_sapi_name(), 0, 3)) == 'cli';
}

/**
 * dir exists check
 * @param mix
 * 	  @example
 *     1. "/a/b/c" create dir if not exists
 *     2. array("/a/b/c") create dir if not exists
 *     3. array("/a/b/c", 0777) create dir if not exists & chmod with 0777
 * ...
 * ...
 * ...
 */
function cli_dir_exists_check() {
	if(!is_cli()) { return; }
	$vars = func_get_args();
	foreach ($vars AS $dir) {
		!is_array($dir) && $dir = array($dir);
		clearstatcache();
		!is_dir($dir[0]) && mkdir($dir[0], 0755, true);
		if(!empty($dir[1])) {
		    $stat = stat($dir[0]);
		    $mode = base_convert(substr(decoct($stat['mode']), -4), 8, 10);
		    $mode != $dir[1] && chmod($dir[0], $dir[1]);
		}
	}
}

/**
 * get log object
 * @param string $log_filename
 * @return Log object
 */
function get_log_handle($log_filename) 
{
	global $_SC, $_SCONFIG;
	$log_options = array(
		'debug'            => $_SC['log_level'],
		'backtrace'        => $_SC['log_func_backtrace'],
		'echo_log'         => $_SC['log_in_shell'],
		'folder_per_month' => $_SC['log_split_folder_per_month'],
		'file_per_day'     => $_SC['log_split_file_per_day'],
	);
	return new Log($log_filename, S_LOG, $log_options);
}

/**
 * gearman client do
 * @param obj    $client
 * @param string $func
 * @param string $workload
 */
function gearman_client_dobackground($client, $func, $workload) 
{
	$job_handle = $client->doBackground($func, $workload);
	$return_code = $client->returnCode();
	return $client->returnCode() == GEARMAN_SUCCESS;
}

/**
 * get gearman client
 */
function gearman_get_client()
{
	global $_SC, $_SCONFIG;
	$client = new GearmanClient();
	$client->addServers($_SC['gearman_server']);
	return $client;
}


/**
 * htmlspecialchars
 * 
 * @param mixed $var
 * 
 * @return mixed
 */
function h($var)
{
    if( is_array($var) ) {
        foreach ($var as $key=>$value) {
            $var[$key] = h($value);
        }
    } else {
        $var = htmlspecialchars($var);
    }
    return $var;
}

/**
 * var_dump var
 * 
 * @param mixed $var 需要打印的变量
 * @param bool $halt 是否在此中断
 *  
 */
function pr($var, $halt = true)
{
	static $is_print_css = null;
	
	$backtrace = debug_backtrace();
	
	if( is_null($is_print_css) ) {
		echo<<<EOF
<style>
body { color:#fff;background-color:#3c3c3c; }
a { color:#94aefb; }
.func { font-weight:bold;color:#1ad77c; }
.trace_header { background-color:#515252;padding:5px;font-size:12px; }
.var { color:#f9dd1d;margin:3 0 30 20px;border-left:2px solid #88a3f2;background-color:#515252;padding:5px;font-weight:500;font-size:14px; }
.trace { border-left:3px solid #39c4dd;padding-left:2px; }
</style>
EOF;
	        $is_print_css = true;
	}
	
	// 函数堆栈
	echo "<div class='trace_header'>";
	$i=0;
	foreach ( $backtrace as $key=>$val ) {	   
	   echo "<div class='trace' style='margin-left:".($i*50)."px;'>";
	   $path_info = pathinfo($val['file']);
	   echo "<span class='func'>{$val['function']}()</span>, <a href='#' onclick='return false;' title=\"".h($val['file'])."\"><b>".($path_info['basename'])."</b></a>: <b>{$val['line']}</b>";
	   echo "</div>";
	   $i++;
	}	
	echo "</div>";
	
	// 变量信息
	echo "<pre class='var'><code>";
    var_dump($var);
    echo '</pre></div>';
    
    //echo "</div>";
    
    if( $halt ) exit(1);
}



/**
 * connect to mongodb
 */
function connect_mongodb($dbname = null) {
	global $_SC, $_SGLOBAL;
	is_null($dbname) && $dbname = $_SC['MongoDB']['dbname'];
	do{
		try {
			$db = new MongoHandle($_SC['MongoDB']['server'], $dbname, $_SC['MongoDB']['options']);
			
		} catch (MongoConnectionException $e) {
			$_SGLOBAL['logger']->error('Mongodb connect error:'.$e->getMessage().' sleep 5');
			sleep(5);
			continue;
		}
		return $db;
	} while(1);
	return null;
}

















