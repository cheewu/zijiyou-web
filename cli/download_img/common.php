<?php
/**
 * common file
 * 
 * @author HouRui
 * @since 2012-04
 */
// set memory_limit
ini_set('memory_limit', '128M');
// IN_SYSTEM
define('IN_SYSTEM', 1);
// S_ROOT
define('S_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);
// set include_path
ini_set('include_path', '.'.PATH_SEPARATOR.
							S_ROOT.PATH_SEPARATOR.
							S_ROOT.'include'.DIRECTORY_SEPARATOR.PATH_SEPARATOR.
							S_ROOT.'library'.DIRECTORY_SEPARATOR);
// socket timeout
ini_set('default_socket_timeout', 5);
// timezone
date_default_timezone_set('Asia/Shanghai');

/********************  dir path const  *******************/
// log dir path
define('S_LOG', S_ROOT.'log'.DIRECTORY_SEPARATOR);
// var dir path
define('S_VAR', S_ROOT.'var'.DIRECTORY_SEPARATOR);
// image dir path
define('S_IMG', S_ROOT.'image_split'.DIRECTORY_SEPARATOR);
// image dir path
define('S_POI_IMG', S_ROOT.'googleImages'.DIRECTORY_SEPARATOR);
// pid dir path
define('S_PID', S_VAR.'pid'.DIRECTORY_SEPARATOR);
// data dir path
define('S_DAT', S_VAR.'data'.DIRECTORY_SEPARATOR);

/*****************  require essential files  ******************/
// include base config
require_once 'config.php';
require_once 'config_run.php';

// include function frame
require_once 'include/function_frame.php';

// include dir
require_folder('include', S_ROOT);
require_folder('library', S_ROOT);

/*****************  extension check  ******************/
extension_check($_SCONFIG['extensions']);

/*****************  dir exist check  ******************/
cli_dir_exists_check(S_LOG, S_VAR, S_PID, S_DAT, S_IMG);

/******************  data pretreat  *******************/
// init running vars
$_SGLOBAL = array();//

