<?php

$config['template_folder'] = 'sep';

$config['search_category'] = array(
	'attraction' => '景点',
	'article' => '游记',
	'traffic' => '交通',
	'stay' => '住宿',
	'shop' => '购物',
	'food' => '美食',
);

$config['search_region_map_category'] = array(
	'pano' => '图片',
	'attraction' => '景点',
	'shoppingcenter' => '购物中心',
	'airport' => '飞机场',
	'subway' => '地铁站',
	'train' => '火车站',
);

/* mongodb */
$config['mongo'] = array(

//	'm_host' => '192.168.0.184',
	'm_host' => '127.0.0.1',

	'm_dbname' => 'tripfm',
	'm_port' => '27017',
);

/* solr 接口 */
$config['solr'] = array(
//	'solr_url' => 'http://192.168.0.184',
	'solr_url' => 'http://125.34.0.255',
	'solr_port' => '8080',
	'solr_url_suffix' => '/solr/',
	//针对不同分组 article与note使用目录不同
	'solr_url_group' => array(
		'article' => 'core0/',
		'note' => 'core1/',	
		'weibo' => 'core2/',
		'multi' => 'core3/',
	),
	'solr_query_type' => array(
		'ct' => 'dismax/',
//		'multi' => 'simple/',
		'basic' => 'select/',
	),
	'solr_basic_query' => array(
//		'indent' => 'on',
//		'version' => '2.2',
//		'fl' => '*,score',
//		'qf' => 'title^5.0+content^1.0',
	),
	'solr_default_ps' => 10,
);

/* poi 分类 */
$config['poi'] = array(
	'category' => array(
		'attraction' => '景点',
		'subattraction' => '子景点',
		'train' => '火车站',
		'airport' => '机场',
		'bus' => '长途车站',
		'subway' => '地铁站',
		'resturant' => '餐馆',
		'shoppingcenter' => '购物中心',
		),
	'default_ps' => 20,
		
	'dbfield' => array(
		'area' => '区域',
		'englishName' => '英文称',
		'keyword' => '关键词',
		'ticket' => '门票',
		'telNum' => '电话',
		'address' => '地址',
		'openTime' => '营业时间',
		'images' => '图片',
		'website' => '网站',
		'weibo' => '微博',
		'blog' => '博客',
		'desc' => '描述',
	),
);

/* region 分类 */
$config['region'] = array(
	'category' => array(   
		'destination' => '旅游目的地',
		'country' => '国家',
		'province' => '省',
		'city' => '市',
		'town' => '县',
		'other' => '其他',
		),
	'default_ps' => 20,
	
	'dbfield' => array(
		'area' => '区域',
		'timezone' => '时区',
		'keyword' => '关键词',
		'address' => '地址',
		'website' => '网站',
		'weibo' => '微博',
		'blog' => '博客',
		'desc' => '描述',
	),
);


/* 微博 */
$config['weibo'] = array(
	'appkey' => '2473630084',
	'appsecret' => '544cffa5983edfd272c4c70483c71bc5',
	'token' => 'bd754b69c9cb833f6a0ec82ebfdda0aa',
	'oauth_token' => 'de8ac552394c38043f3458de3758b4d1',
);

/* POI->Region category */
$config['poi_to_region_category'] = array(
	'attraction' => array('attraction'),
	'traffic' => array('airport', 'train', 'subway'),
	'shop' => array('shoppingcenter'),
);

/* 日期筛选 */
$config['month_filter'] = array(
	'1' => '一个月内',
	'3' => '三个月内',
	'6' => '六个月内'
);

