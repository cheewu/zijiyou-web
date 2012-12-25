<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "search";

/* handle 
$route['region_handle(.*)'] = "search/region$1";
$route['poi_handle(.*)'] = "search/poi$1";
*/
/* Region页 
$route['region/([^_]+)/?'] = "search/region/name=$1";
$route['region/([^_]+)_([^p][^/]*)/?'] = "search/region/name=$1&area=$2";
$route['region/([^_]+)_p(\d+)/?'] = "search/region/name=$1&pg=$2";
$route['region/([^_]+)_([^p][^/]*))_p(\d+)/?'] = "search/region/name=$1&area=$2&pg=$3";
*/
/* 游记页 
$route['article/([^_]+)/?'] = "search/article/name=$1";
$route['article/([^_]+)/(.+)'] = "search/article/name=$1&q2=$2";
$route['article/([^_]+)_([^p][^/]*)/?'] = "search/article/name=$1&area=$2";
$route['article/([^_]+)_([^p][^/]*)/(.+)'] = "search/article/name=$1&area=$2&q2=$3";
$route['article/([^_]+)_p(\d+)/?'] = "search/article/name=$1&pg=$2";
$route['article/([^_]+)_p(\d+)/(.+)'] = "search/article/name=$1&pg=$2&q2=$3";
$route['article/([^_]+)_([^p][^/]*))_p(\d+)/?'] = "search/article/name=$1&area=$2&pg=$3";
$route['article/([^_]+)_([^p][^/]*))_p(\d+)/(.+)'] = "search/article/name=$1&area=$2&pg=$3&q2=$4";
*/
/* Attraction页 
$route['attraction/([^_]+)/?'] = "search/attraction/name=$1";
$route['attraction/([^_]+)_([^p][^/]*)/?'] = "search/attraction/name=$1&area=$2";
$route['attraction/([^_]+)_p(\d+)/?'] = "search/attraction/name=$1&pg=$2";
$route['attraction/([^_]+)_([^p][^/]*))_p(\d+)/?'] = "search/attraction/name=$1&area=$2&pg=$3";
*/
/* 分类游记页 
$route['note/([^_]+)_?([^_]*)_?p?(\d*)/?'] = "search/note/name=$1&area=$2&pg=$3";
$route['note/([^_]+)/?'] = "search/note/name=$1";
$route['note/([^_]+)_([^p][^/]*)/?'] = "search/note/name=$1&area=$2";
$route['note/([^_]+)_p(\d+)/?'] = "search/note/name=$1&pg=$2";
$route['note/([^_]+)_([^p][^/]*))_p(\d+)/?'] = "search/note/name=$1&area=$2&pg=$3";
*/
/* POI页 
$route['poi/([^_]+)_?([^_]*)_?p?(\d*)/?'] = "search/poi/name=$1&area=$2&pg=$3";
$route['poi/([^_]+)/?'] = "search/poi/name=$1";
$route['poi/([^_]+)_([^p][^/]*)/?'] = "search/poi/name=$1&area=$2";
$route['poi/([^_]+)_p(\d+)/?'] = "search/poi/name=$1&pg=$2";
$route['poi/([^_]+)_([^p][^/]*))_p(\d+)/?'] = "search/poi/name=$1&area=$2&pg=$3";
*/
$route['404_override'] = '';
/* End of file routes.php */
/* Location: ./application/config/routes.php */