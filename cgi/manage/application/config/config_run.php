<?php
/* poi */
$config['poi_field']['text'] = array(
	'area' => '地域(对应region.Name)',
	'name' => '名称',
	'englishName' => '英文名',
    'center'    => '坐标',
	'wikititle' => '维基百科条目',
	'ticket' => '门票',
	'telNum' => '电话',
	'keyword' => '关键词',
	'website' => '网站',
	'blog' => '博客',
	'weibo' => '微博',
	'openTime' => '营业时间',
);
//$config['poi_field']['textarea'] = array(
//	'film' => '电影',
//	'people_interred' => '人物',
//	'artwork' => '艺术品',
//);
$config['poi_field']['option'] = array(
	'category' => '分类',
);
$config['poi_field']['json'] = $config['poi_field']['textarea'];
$config['poi_field']['list'] = $config['poi_field']['text'];
$config['poi_field']['list']['address'] = '地址';
$c = &$config['poi_field']['list'];
unset($c['keyword'], $c['blog'], $c['weibo'], $c['website']);

$config['poi_field']['disable'] = array(
	'area'
);

/* Region */
$config['region_field']['text'] = array(
	'area' => '地域',
	'name' => '名称',
	'englishName' => '英文名',
	'wikititle' => '维基条目',
	'is_important' => '是否为热门',
	'timezone' => '时区',
	'keyword' => '关键词',
	'website' => '网站',
	'blog' => '博客',
	'weibo' => '微博',
	'openTime' => '营业时间',
	'poi_cnt' => 'poi数(动态计算)',
    'poi_pic' => 'poi图片修改',
);
$config['region_field']['textarea'] = array(
	'film' => '电影',
	'transportation' => '到达离开',
	'wikicategory' => '维基百科分类',
);
$config['region_field']['option'] = array(
	'category' => '分类',
);
$config['region_field']['list'] = $config['region_field']['text'];
$config['region_field']['list']['edit'] = '编辑';

$config['region_field']['json'] = array(
	'film' => '电影',
	'transportation' => '到达离开',
);

$config['region_field']['xml'] = array(
	'wikicategory' => '维基百科分类',
);

$config['region_field']['wikicategory'] = array('food', 'transportation');

$config['region_field']['disable'] = array(
	'poi_cnt'
);
$config['region_field']['list_search_category'] = array(
	'area', 'name'
);

/* Article */
$config['article_field']['text'] = array(
	'title' => '标题',
	'line' => '路线',
	'tag' => '标签',
	'publishDate' => '时间',
);
$config['article_field']['textarea'] = array(
	'content' => '正文',
);
$config['article_field']['option'] = array(
	'is_recommend' => '是否推荐',
);
$config['article_field']['list'] = $config['article_field']['text'];
$config['article_field']['list']['content'] = '正文'; 
$config['article_field']['list']['edit'] = '编辑';

/* wikipedia */
$config['wikipedia_field']['text'] = array(
	'title' => '标题',
	'tag' => '标签(以英文逗号分割)',
);
$config['wikipedia_field']['textarea'] = array(
	'content' => '正文',
);
$config['wikipedia_field']['list'] = $config['wikipedia_field']['text'];
$config['wikipedia_field']['list']['content'] = '正文'; 
$config['wikipedia_field']['list']['edit'] = '编辑';

/* search */
$config['search']['category'] = array(
	'Region','POI','Article','Wikipedia'
);
$config['search']['order'] = array(
	'Region' => array('name' => -1),
	'POI' => array('rank' => -1),
);
$config['search']['query_default'] = array(
	'pg' => 1,
	'ps' => 20,
);
$config['search']['mongo_query_default'] = array(
    '$or' => array(
        array('is_del' => array('$exists' => false)), 
        array('is_del' => false)
     ),
);














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

	'm_host' => '202.85.213.54',
//	'm_host' => '127.0.0.1',

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

