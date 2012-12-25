<?php
/**
 * Config Router
 * 
 * @author panzhibiao & HouRui
 */
if(!defined('IN_SYSTEM')) { exit('Access Denied'); }

/* router */
$_SCONFIG['router'] = array(
	/**
	 * array('pattern', 'replace_pattern', 'flag')
	 *   @param flag: enom(redirect, permanent, continue, break) default: break
	 *   			  continue - 继续从头循环遍历, break - 跳出路由匹配
	 *   
	 *   @example
	 *   	target: /index/2134/ -> /index/list/?list_id=2134
	 *   	router: array("/^\/index\/(\d+)\/?/i", "/index/list/?list_id=${1}", 'break'),
	 */
	array('#^/?state/([0-9a-zA-Z]{24})/?#', '/state/?region_id=${1}', 'break'),
	array('#^/?region/([0-9a-zA-Z]{24})/?#', '/region/?region_id=${1}', 'break'),
	array('#^/?poi/([0-9a-zA-Z]{24})/?#', '/poi/?poi_id=${1}', 'break'),
	array('#^/?attraction/([0-9a-zA-Z]{24})/?#', '/attraction/?region_id=${1}', 'break'),
	array('#^/?article/([0-9a-zA-Z]{24})/?#', '/article/?region_id=${1}', 'break'),
	array('#^/?map/([0-9a-zA-Z]{24})/?#', '/map/?region_id=${1}', 'break'),
	array('#^/?detail/([0-9a-zA-Z]{24})/([0-9a-zA-Z]{24})/?#', '/article/detail/?region_id=${1}&article_id=${2}', 'break'),
	array('#^/?fragement/([0-9a-zA-Z]{24})/([0-9a-zA-Z]{24})/?#', '/article/fragement/?region_id=${1}&fragement_id=${2}', 'break'),
	array('#^/?wiki/([0-9a-zA-Z]{24})/([0-9a-zA-Z]+)/?#', '/wiki/?region_id=${1}&wiki_id=${2}', 'break'),
	array('#^/?ajax/region/([0-9a-zA-Z]{24}).json#', '/ajax/region/${1}', 'break'),
	
	array('#^/?mobile/article/(region|poi)/([0-9a-zA-Z]{24})/?#', '/article/?mobile=1&id=${2}&type=${1}', 'break'),
);
/* /router */