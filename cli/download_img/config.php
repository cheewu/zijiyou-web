<?php
/**
 * Config
 * 
 * @author HouRui
 * @since 2011-09
 * 
 */
if(!defined('IN_SYSTEM')) {	exit('Access Denied'); }

/*
 * $_SC vars
 */
$_SC = array();// init $_SC

/*
 * test mode level：(INT)
 * 0 = test mode is switch off
 * 1 = all receiver email address will be replace with $_SCONFIG['default_msg_to'] (in config_run.php)
 *     also the event_id will be add to the subject as [event_id]subject
 * 2 = include level 1, 
 *     the status & last_modified_date field will not be modified & no job log write & no send counter write
 * 3 = include level 1 & 2, 
 * 	   the loop will be execute only once, 
 */
$_SC['test_mode_level'] = 0;/* defalut:[int 0] */

/*
 * log out put level：(INT)
 * 0 = nothing
 * 1 = PID & ERROR only
 * 2 = PID & ERROR & NOTICE
 * 3 = PID & ERROR & NOTICE & INFO
 */
$_SC['log_level'] = 3;/* default:[int 1] */

/*
 * is throw exception
 */
$_SC['is_throw_exception'] = false;/* default:[bool false] */

/*
 * backtrace of func in log
 */
$_SC['log_func_backtrace'] = false;/* defalut:[bool false] */

/*
 * log in shell instead of write file
 */
$_SC['log_in_shell'] = false;/* defalut:[bool false] */

/*
 * split log by folder per month
 */
$_SC['log_split_folder_per_month'] = false;/* defalut:[bool false] */

/*
 * split log by file per day
 */
$_SC['log_split_file_per_day'] = true;/* defalut:[bool true] */

/*
 * memcaehd servers
 */
$_SC['memcached_server'] = array(
	array('127.0.0.1', 11211),
);

/*
 * gearman servers
 */
$_SC['gearman_server'] = "127.0.0.1:4730";

// this system DB
$_SC['MongoDB'] = array(
	'server'	=> 'mongodb://202.85.213.54:27017',
	'dbname'	=> 'page',
	'options'	=> array('username' => 'admin', 'password' => 'iamzijiyou', 'safe' => true),
);








