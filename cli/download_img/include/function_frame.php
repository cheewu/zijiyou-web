<?php
/**
 * Function - frame
 * 
 * @author HouRui
 * @since 2012-04
 */
if(!defined('IN_SYSTEM')) {	exit('Access Denied'); }

/**
 * require folder
 * @param mix $folder
 * @param string $path  
 */
function require_folder($folder, $path, $is_recursive = false){
	clearstatcache();
	!is_array($folder) && $folder = array($folder);
	foreach($folder AS $pre_folder){
		$path_ = $path.$pre_folder.DIRECTORY_SEPARATOR;
		$file_name_arr =  scandir($path_);
		foreach($file_name_arr AS $file_name){
			// php file only
			if(strtolower(substr($file_name, -3)) != 'php') { continue; }
			$pre_file = $path_.$file_name;
			require_file($pre_file);
		}
	}
}

/**
 * require file
 * @param mix $folder
 * @param string $path  
 */
function require_file($pre_file){
	clearstatcache();
	global $_SC, $_SCONFIG, $_SGLOBAL;//所有全局变量
	is_file($pre_file) && require_once $pre_file;
}