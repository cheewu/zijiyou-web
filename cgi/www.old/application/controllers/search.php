<?php

class Search extends CI_Controller {
	
	private $page_db;
	
	public function __construct() {
		parent::__construct();
		$this->page_db = new Mongo_db(array('dbname' => 'page'));
	}
	
	public function index($category = null)
	{
		$get = $this->input->get();
		if(empty($get)){
			$param = array(
				'is_index' => TRUE,
			);
			template('index', $param);
			return;
		}
		//分析查询词
		$q = explode(", ", $get['q']);
		//查询内容
		$query = array();
		$query['name'] = $q[0];
		!empty($q[1]) && $query['area'] = $q[1];
		
		if($region = $this->mongo_db->Region_fetch($query)){
			header("Location: /search/region/".$region['_id']);
			return;
		}
		if($poi = $this->mongo_db->POI_fetch($query)){
			header("Location: /search/poi/".$poi['_id']);
			return;
		}
		header("Location: /");
		return;
		
		
		
		
		
		
		
		$keyword_relat_res = $this->mongo_db->get_relative_keyword($get['q']);
	
		$solr_config = $this->config->item('solr');
		
		if(!empty($category) && $category != 'article')
		{
			$category_arr = $this->config->item('search_category');
			$extra_options = array('ct' => $category_arr[$category]);
		}
		$this->solr_request->init();
		$this->solr_request->parse_request($extra_options);
		$reponse = $this->solr_request->do_request();
		
		$result = array();
		if(empty($category) || $category == 'article'){
			//请求article
			$show_field = array('title', 'url', 'content', 'publishDate', 'originUrl');
			foreach($reponse['response']['doc'] AS $key => $vlaue){
				//从mongodb中获取信息
				$tmp = $this->page_db->Article_fetch_by__id($vlaue['_id'], $show_field);
				$result[$key] = array_merge($vlaue, $tmp);
				
			}
		}else{
			//请求note
			foreach($reponse['response']['doc'] AS $key => $vlaue){
				//从mongodb中获取信息
				$tmp = $this->mongo_db->Note_fetch_by__id($vlaue['_id']);
				$result[$key] = array_merge($vlaue, $tmp);
			}
		}
//		pr($result);
/*		
		$map_marker_desc = '<div>';
		$poi_info = $this->mongo_db->POI_fetch_by_name($get['q']);
		if(!empty($poi_info)){
			$poi_config = $this->config->item('poi');
			foreach($poi_config['dbfield'] AS $key => $value){
				$map_marker_desc .= !empty($poi_info[$key]) ? '<b>'.$value.'</b>:'.google_map_strip_char(strip_tags($poi_info[$key])).'</br>' : '';
			}
		}
		$region_info = $this->mongo_db->Region_fetch_by_name($get['q']);
		if(!empty($region_info)){
			$region_config = $this->config->item('region');
			foreach($region_config['dbfield'] AS $key => $value){
				$map_marker_desc .= !empty($region_info[$key]) ? '<b>'.$value.'</b>:'.google_map_strip_char(strip_tags($region_info[$key])).'</br>' : '';
			}
		}
		$map_marker_desc .= '</div>';
*/	

//		pr($map_marker_desc);
//		pr($reponse_article['response']['doc']);
		$total_cnt = $reponse['response']['numFound'];
		$pg = $get['pg'] ?: 1;
		$title = trim($get['q']);
		$title .= trim($get['ct']) ?: '';
		//拼接url
		$url = "/search/{$category}?q={$get['q']}";
		$param = array(
			'category' => $category,
			'result' => $result,
			'relative_keyword' => $keyword_relat_res,
			'pg' => $pg,
			'title' => $title,
			'get' => recursive_rawurldecode($get),
//			'region_id' => $region_info['_id'],
//			'poi_id' => $poi_info['_id'],
//			'map_marker_desc' => $map_marker_desc,
			'multi' => multi($total_cnt, $solr_config['solr_default_ps'], $pg, $url),
		);
//		pr($this->users_handle->user_detail);
		template(empty($category) ? 'region' : 'article_note', $param);
	}
	
	public function traffic($region_id)
	{
		$this->note('traffic', $region_id);
	}
	
	public function stay($region_id)
	{
		$this->note('stay', $region_id);
	}
	
	public function shop($region_id)
	{
		$this->note('shop', $region_id);
	}
	
	public function food($region_id)
	{
		$this->note('food', $region_id);
	}
	
	private function check_region($name)
	{
//		$region_check = $this->mongo_db->Region_fetch_by_name($name);
//		empty($region_check) && header("Location: /search/?q={$name}");
	}
	
	
	private function note($category, $region_id)
	{
		$region_id = rawurldecode($region_id);
		$region = $this->mongo_db->Region_fetch_by__id($region_id);
		
		$get = $this->input->get();
		$crumbs = array();
		$name = $region['name'];
		$this->check_region($name);
		
		//面包屑
		$crumbs = $this->get_crumbs($region['_id'], $category);
//		pr($region);
		$poi_arr = $this->mongo_db->POI_fetchall(array('regionId' => new MongoID($region['_id'])));
		$poi_cate_arr = array();
		$poi_category = $this->config->item('search_category');
		$poi_to_region_category = $this->config->item('poi_to_region_category');
		
		$geo_arr = array();
		if(!empty($poi_to_region_category[$category])){
			foreach($poi_arr['item'] AS $key => $value){
				if( empty($value['center']) || (empty($value['center'][0]) && empty($value['center'][1])) ){continue;}
				if(in_array($value['category'], $poi_to_region_category[$category])){
					$poi_cate_arr[$value['category']][] = $value;
					$geo_arr[$value['_id']] = array(
						'title' => $value['name'],
						'content' => $this->get_geo_content($value),
						'position' => array('lt' => $value['center'][0], 'lg' => $value['center'][1]),
					);
				}
			}
		}
		
		$or = !empty($region['keyword']) ? explode(",", $region['keyword']) : array($name);
		$or = recursive_trim($or);
		
		$solr_zct_cate = $this->config->item('search_category');
		
		$this->solr_request->init();
		$this->solr_request->parse_request(array('ps' => 10, 'ct' => $solr_zct_cate[$category], 'q' => implode("OR", $or)));
		$reponse = $this->solr_request->do_request();
		
//		pr($reponse);
		
		$note = array();
		foreach($reponse['response']['doc'] AS $value){
			$note[] = array_merge($this->page_db->Article_fetch_by__id($value['_id']), array('publishDate' => $value['publishDate']));
		}
		
		$multi = multi($reponse['response']['numFound'], 10, $get['pg'] ?: 1, '/search/'.$category.'/'.$region_id);
		
		$param = array(
			'crumbs' => $crumbs,
			'name' => $name,
			'category' => $category,
			'poi_cate_arr' => $poi_cate_arr,
			'note' => $note,
			'multi' => $multi,
			'poi_category' => $poi_category,
			'geo_arr' => $geo_arr,
			'region' => $region,
			'highlighting' => $reponse['highlighting'],
//			'pg' => $pg,
//			'region_id' => $region_info['_id'],
//			'poi_id' => $poi_info['_id'],
//			'map_marker_desc' => $map_marker_desc,
		);
//		pr($poi_cate_arr);
		template('note', $param);
	}
	
	public function article($region_id)
	{
		$get = $this->input->get();
		
		$region_id = rawurldecode($region_id);
		$region = $this->mongo_db->Region_fetch_by__id($region_id);
		$name = $region['name'];
		
		//请求url
		$base_url = '/search/article/'.$region_id;
		//请求参数
		$url_sets = array();
		//面包屑
		$crumbs = $this->get_crumbs($region['_id'], 'article');
		
		//or查询查询词
		$or = array();
		//tag
		$tags = array();
		if(!empty($get['q2'])){
			$tags = explode(" ", $get['q2']);
			$tags = recursive_trim($tags);
			foreach($tags AS $key => $value){
				if(empty($value)){
					unset($tags[$key]);
				}
			}
			$q2 = implode('+', $tags);
		}else{
			$q2 = ''; 
		}
		
		$or = !empty($region['keyword']) ? explode(",", $region['keyword']) : array($name);
		$or = recursive_trim($or);
		
		$q = implode("OR", $or);
		!empty($tags) && $q .= 'AND'.implode("AND", $tags);
		
		$this->solr_request->init();
		$this->solr_request->parse_request(array(
				'ps' => 10, 
				'q' => $q, 
				'fdr' => 1
		));
		$reponse = $this->solr_request->do_request();
		
		$article = array();
		foreach($reponse['response']['doc'] AS $value){
			$article[] = $this->page_db->Article_fetch_by__id($value['_id']);
		}
		
		//时间筛选
		!empty($get['dr']) && $url_sets['dr'] = $get['dr'];
		
		//翻页url
		$no_pg_url = implode_url_set($base_url, $url_sets);
		!empty($q2) && $no_pg_url = url_append($no_pg_url, 'q2', $q2) ;
		$multi = multi($reponse['response']['numFound'], 10, $get['pg'] ?: 1, $no_pg_url);
		
		//最终article url
		!empty($get['pg']) && $url_sets['pg'] = $get['pg'];
		$article_url = implode_url_set($base_url, $url_sets);
		!empty($q2) && $article_url = url_append($article_url, 'q2', $q2);
		
		//加入q2
		$url_sets['q2'] = $q2;
		
		$param = array(
			'crumbs' => $crumbs,
			'name' => $name,
			'category' => 'article',
			'article' => $article,
			'multi' => $multi,
			'get' => $get,
			'keyword' => explode(" ", $reponse['dynamicKeywords']),
			'tags' => $tags,
			'article_url' => $article_url,
			'highlighting' => $reponse['highlighting'],
			'region' => $region,
			'url_sets' => $url_sets,
			'base_url' => $base_url,
//			'pg' => $pg,
//			'region_id' => $region_info['_id'],
//			'poi_id' => $poi_info['_id'],
//			'map_marker_desc' => $map_marker_desc,
		);
//		pr($poi_cate_arr);
		template('article', $param);
	}	
	
	public function attraction($region_id){
		
		$get = $this->input->get();
		
		$region_id = rawurldecode($region_id);
		$region = $this->mongo_db->Region_fetch_by__id($region_id);
		$name = $region['name'];
		
		//面包屑
		$crumbs = $this->get_crumbs($region['_id'], 'attraction');
		
		//获取地理信息
		$geo = !empty($region['center']) && (!empty($region['center'][0]) || !empty($region['center'][1])) ? array('lt' => $region['center'][0], 'lg' => $region['center'][1]) : null;
		
		$poi_attraction = $this->mongo_db->POI_fetchall(array('regionId' => new MongoID($region['_id']), 'category' => 'attraction'), null, array('rank' => -1), 10, $get['pg'] ?: 1); //, 'center' => array('$near' => $region['center'])
		$geo_arr = $attraction = array();
		foreach($poi_attraction['item'] AS $value){
			//筛选有坐标点的poi
			if(empty($value['center'][0]) && empty($value['center'][1])){continue;}
			//筛选只有中文的poi
			if(mb_strlen($value['name'], 'utf-8') == strlen($value['name'])){continue;}
			/* 处理google地图信息 */
			$geo_arr[$value['_id']] = array(
				'position' => array('lt' => $value['center'][0], 'lg' => $value['center'][1]),
				'title' => $value['name'],
			); 
			$geo_arr[$value['_id']]['content'] = $this->get_geo_content($value);
			$attraction[$value['_id']] = $value;
			/* 处理google地图信息 end*/
		}
		
		$sub_info = $this->mongo_db->POI_fetchall(array('regionId' => new MongoID($region['_id']), 'center' => array('$exists' => true), 'category' => array('$ne' => 'attraction')), null, array('rank' => -1));
		$sub_geo_arr = array();
		foreach($sub_info['item'] AS $value){
			//筛选有坐标点的poi
			if(empty($value['center'][0]) && empty($value['center'][1])){continue;}
			//筛选只有中文的poi
			if(mb_strlen($value['name'], 'utf-8') == strlen($value['name'])){continue;}
			/* 处理google地图信息 */
			$sub_geo_arr[$value['category']][$value['_id']] = array(
				'position' => array('lt' => $value['center'][0], 'lg' => $value['center'][1]),
				'title' => $value['name'],
			); 
			$sub_geo_arr[$value['category']][$value['_id']]['content'] = $this->get_geo_content($value);
			/* 处理google地图信息 end*/
		}
		
		
		$multi = multi($poi_attraction['count'], 10, $get['pg'] ?: 1, '/search/attraction/'.$region_id);
//		pr($attraction);
		$param = array(
			'region' => $region,
			'crumbs' => $crumbs,
			'name' => $name,
			'category' => 'attraction',
			'geo' => $geo,
			'geo_arr' => $geo_arr,
			'sub_geo_arr' => $sub_geo_arr,
			'attraction' => $attraction,
			'multi' => $multi,
		);
		template('attraction', $param);
	}
	
	
	
	public function collection()
	{
		if(!$this->users_handle->user_is_login()){
			header("Location: /user/login");
		}
		$coming_url = $_SERVER['HTTP_REFERER'];
		$get = $this->input->get();
		if(empty($get['cate']) || $get['cate'] == 'article'){
			//请求article
			$show_field = array('title', 'url', 'content', 'publishDate', 'originUrl');
			$result = $this->page_db->Article_fetch_by__id($get['id'], $show_field);
		}else{
			//请求note
			$result = $this->mongo_db->Note_fetch_by__id($get['id']);
		}
		$param = array(
			'result' => $result,
			'coming_url' => $coming_url,
		);
		template('pre_edit_article_note', $param);
	}
	
	
	public function collect_pre_modify()
	{
		if(!$this->users_handle->user_is_login()){
			header("Location: /user/login");
		}
		$get = $this->input->get();
		//collection cate对应关系
		$type_options = array(
			'note' => 'Note',
			'arti' => 'Article',
			'regi' => 'Region',
			'poi' => 'POI',
		);
		$type = $type_options[$get['cate']];
		if($type == 'Article' || $type == 'Note'){
//				$this->mongo_db->selectDB('spiderV21');
				$func_name = $type.'_fetch_by__id';
		}else{
//				$this->mongo_db->selectDB('tripfm');
				$func_name = $type.'_fetch_by__id';
		}
		$item_detail = $this->mongo_db->$func_name($get['id']);
		$param = array(
			'result' => recursive_trim($item_detail),
			'type' => $type,
		);
		if($type == 'POI' || $type == 'Region'){
			$this->load->view(strtolower($type).'_item.php', $param);
		}
		if($type == 'Article' || $type == 'Note'){
//			pr($param);
			$this->load->view('article_note_collect_pre_modify.php', $param);
		}
	}
	
	
	/**
	 * ajax首页autocomplete
	 * @param string $query_word
	 */
	public function query_relative($query_word = null)
	{
		if(!empty($query_word)){
			$query_word = rawurldecode($query_word);
			$query_word = trim($query_word);
		}else{
			$query_word = $this->input->get('q');
		}
		if(!empty($query_word)){
			//查询未删除的
			$is_del = array(array('is_del' => false), array('is_del' => array('$exists' => false)));
			$keyword_res = array();
			$res = $this->mongo_db->Region_fetchall(array('name' => array('$regex' => "^{$query_word}"), 'category' => array('$ne' => 'other'), '$or' => $is_del), array('name', 'area'), null, 9);
			foreach($res['item'] AS $key => $value){
				$tmp = $value['name'];
				$value['area'] = trim($value['area']);
				!empty($value['area']) && $tmp .= ", ".$value['area'];
				$keyword_res[] = $tmp;
			}
			$res = $this->mongo_db->POI_fetchall(array('name' => array('$regex' => "^{$query_word}"), '$or' => $is_del), array('name', 'area', 'regionId'), null, 9);
			foreach($res['item'] AS $key => $value){
				if(!empty($value['regionId'])){
					$main_region = $this->mongo_db->Region_fetch_by__id((string)$value['regionId']);
				}
				$tmp = $value['name'];
				$main_region['name'] = trim($main_region['name']);
				!empty($main_region['name']) && $tmp .= ", ".$main_region['name'];
				$keyword_res[] = $tmp;
			}
			if(!empty($keyword_res)){
				foreach($keyword_res AS $key => $value){
					if($key >= 8 ){break;}
					echo $value."\n";
				}
			}
		}
	}
	
	
	public function region($region_id)
	{
		$get = $this->input->get();
		
		$region_id = rawurldecode($region_id);
		$region = $this->mongo_db->Region_fetch_by__id($region_id);
		$name = $region['name'];
		
		$transportation = array();
		foreach($region['transportation'] AS $key => $value){
			foreach(explode(",", $value) AS $id){
				$transportation[] = $this->mongo_db->POI_fetch_by__id($id);
			}
		}
		
		//面包屑
		$curmbs = $this->get_crumbs($region['_id']);
		/*
		//or查询的内容
		$or = array();
		//有keyword用keyword，没有就用name
		$or = !empty($region['keyword']) ? explode(",", $region['keyword']) : array($name);
		*/
		$this->solr_request->init();
		$this->solr_request->parse_request(array('ps' => 15, 'weibo' => true, 'q' => $name));
		$reponse = $this->solr_request->do_request();
		
		$weibo = array();
		$weiboDB = new Mongo_db(array('dbname' => 'weiboDB'));
		//weibo
		$weibo_config = $this->config->item('weibo');
		$wb_client = new WeiboClient($weibo_config['appkey'], $weibo_config['appsecret'], $weibo_config['token'], $weibo_config['oauth_token']);
		foreach($reponse['response']['doc'] AS $value){
			$tmp = $weiboDB->Contents_fetch_by__id($value['_id']);
			if(empty($weibo_config['user'][$tmp['uid']])){
				$user_info = $wb_client->show_user($tmp['uid']);
				$weibo_config['user'][$tmp['uid']] = $user_info['profile_image_url'];
			}
			$tmp['user_img'] = $weibo_config['user'][$tmp['uid']]; 
			$weibo[] = $tmp;
		}
		
		$multi = multi($reponse['response']['numFound'], 15, $get['pg'] ?: 1, '/search/region/'.$region_id);
		
		//获取地理信息
		$geo = !empty($region['center']) && (!empty($region['center'][0]) || !empty($region['center'][1])) ? array('lt' => $region['center'][0], 'lg' => $region['center'][1]) : null;
		
//		pr($weibo);
//		pr($article);
		$wiki = get_wiki_content($name);
//		pr($wiki);
		$region_relate = array();
		$correlcation = $this->mongo_db->Correlation_fetchall_by_name($name, null, array('correlation' => -1));
		foreach($correlcation['item'] AS $key => $value){
			$region_relate[$value['category']][] = $value;
		}
//		pr($region_relate['description']);
		$sub_info = $this->mongo_db->POI_fetchall(array('regionId' => new MongoID($region['_id']), 'center' => array('$exists' => true)), null, array('rank' => -1));
		$geo_arr = array();
		$relate_attraction = array();
		foreach($sub_info['item'] AS $value){
			$value['category'] == 'attraction' && $relate_attraction[] = $value;
			//最多30个（景点）
			if($value['category'] == 'attraction' && !empty($geo_arr[$value['category']]) && count($geo_arr[$value['category']]) >= 30){continue;}
			//筛选有坐标点的poi
			if(empty($value['center'][0]) && empty($value['center'][1])){continue;}
			//筛选只有中文的poi
			if(mb_strlen($value['name'], 'utf-8') == strlen($value['name'])){continue;}
			/* 处理google地图信息 */
			$geo_arr[$value['category']][$value['_id']] = array(
				'position' => array('lt' => $value['center'][0], 'lg' => $value['center'][1]),
				'title' => $value['name'],
			); 
			$geo_arr[$value['category']][$value['_id']]['content'] = $this->get_geo_content($value);
			/* 处理google地图信息 end*/
		}
//		pr($geo_arr);
//		pr($region);
		$param = array(
			'crumbs' => $curmbs,
			'geo_arr' => $geo_arr,
			'name' => $name,
			'region' => $region,
			'relate' => $region_relate,
			'address' => $region['name'],
			'map_category' => $this->config->item('search_region_map_category'),
			'keyword' => array('相关景点' => $relate_attraction),
//			'transportation' => $transportation,
			'geo' => $geo,
//			'article' => $article,
			'multi' => $multi,
			'weibo' => $weibo,
			'wiki' => $wiki,
			'highlighting' => $reponse['highlighting'],
		);
//		pr($param);
		template('region', $param);
//		pr($sub_info_arr);
	}
	
	public function poi($poi_id)
	{
		$poi_id = rawurldecode($poi_id);
		//查询poi
		$poi = $this->mongo_db->POI_fetch_by__id($poi_id);
		$name = $poi['name'];
		//get 请求
		$get = $this->input->get();
		//poi分类与region分类关联，将poi挂到某一region分类下
		$poi_region_category = $this->config->item('poi_to_region_category');
		foreach($poi_region_category AS $key => $value){
			if(in_array($poi['category'], $value)){
				$category = $key;
			}
		}
		//获取wiki内容
		$wiki = get_wiki_content($name);
		
		//or查询的内容
		$or = array();
		//有keyword用keyword，没有就用name
		$or = !empty($poi['keyword']) ? explode(",", $poi['keyword']) : array($name);
		$or = recursive_trim($or);
		
		//查询region详细信息
		$region = $this->mongo_db->Region_fetch_by__id((string)$poi['regionId']);
		
		$q = implode("OR", $or);
		
		!empty($region['name']) && $q .= 'AND'.$region['name'];
		//面包屑
		$crumbs = $this->get_crumbs($region['_id'], $category, $poi['_id']);
		//solr查询
		$this->solr_request->init();
		$this->solr_request->parse_request(array('ps' => 10, 'q' => $q));
		$reponse = $this->solr_request->do_request();
		$article = array();
		
		//mongodb中查询详细内容
		foreach($reponse['response']['doc'] AS $value){
			$article[] = array_merge($this->page_db->Article_fetch_by__id($value['_id']), array('publishDate' => $value['publishDate']));
		}
		
		//地铁信息
		$subinfo = array();
		$freebaseDB = new Mongo_db(array('dbname' => 'freebaseDB'));
		if($poi['category'] != 'subway'){
			//最近的地铁站
			$subways_res = $this->mongo_db->mongodb->command(
				array(
					'geoNear' => 'POI', 
					'near' => $poi['center'], 
					'includeLocs' => true, 
					'query' => array('category' => 'subway'),
					'maxDistance' => real_dis_to_lt_lg_dis(2),
					'num' => 5,
				)
			);
			$subinfo['stops'] = array();
			foreach($subways_res['results'] AS $value){
				$tmp = $value['obj'];
				$tmp['dis'] = lt_lg_dis_to_real_dis($value['dis']);
				if(!empty($tmp['lines'])){
					foreach($tmp['lines'] AS $val){
						$line_res = $freebaseDB->transitline_fetch_by__id((string)$val);
						$tmp['stopline'][] = $line_res['name'];
					}
				}
				$subinfo['stops'][] = $tmp;
			}
		}else{
			if(!empty($poi['lines'])){
				$subinfo['lines'] = array();
				foreach($poi['lines'] AS $value){
					$line_res = $freebaseDB->transitline_fetch_by__id((string)$value);
					$subinfo['lines'][] = $line_res['name'];
				}
			}
		}
		
		//相关地点
		$place_nearby_res = $this->mongo_db->mongodb->command(array(
		'geoNear' => 'POI', 
		'near' => $poi['center'], 
		'includeLocs' => true, 
		//'query' => array('category' => 'attraction'), 
		'query' => array('category' => 'attraction', 'regionId' => new MongoID($region['_id'])), 
		'num' => 10)
		);
		//init
		$place_nearby = array();
		foreach($place_nearby_res['results'] AS $value){
			if(!$value['dis']){continue;}
			$tmp = $value['obj'];
			$tmp['dis'] = lt_lg_dis_to_real_dis($value['dis'], 'm');
			$place_nearby[] = $tmp;
		}
		
		//获取地理信息
		$geo = !empty($poi['center']) && (!empty($poi['center'][0]) || !empty($poi['center'][1])) ? array('lt' => $poi['center'][0], 'lg' => $poi['center'][1]) : null;
		/*
		//最近的地铁站
		!empty($geo) && $subway = $this->mongo_db->mongodb->command(array('geoNear' => 'POI', 'near' => $poi['center'], 'includeLocs' => true, 'query' => array('category' => 'subway'), 'num' => 1));
		
		if(!empty($subway)){
			$dis = lt_lg_dis_to_real_dis($subway['results'][0]['dis'], 'm');
			$near_subway = $subway['results'][0]['obj'];
			$near_subway['dis'] = $dis;
		}
		*/
		//分页
		$multi = multi($reponse['response']['numFound'], 10, $get['pg'] ?: 1, '/search/poi/'.$poi_id);
		$param = array(
			'crumbs' => $crumbs,
			'category' => $category,
			'subinfo' => $subinfo,
			'name' => $name,
			'left_set' => array(
				'pre_name' => $region['name'],
			),
			'detail' => $poi,
			'geo' => $geo,
			'hidden_header_nav' => true,
			'article' => $article,
			'wiki' => $wiki,
			'multi' => $multi,
			'place_nearby' => $place_nearby,
			'highlighting' => $reponse['highlighting'],
			'region' => $region,
		);
//		pr($param);
		template('poi', $param);
	}
	
	private function get_geo_content(&$poi){
		//init
		$content = '';
		//取三个类别
		foreach(array('name' => '名称', 'traffic' => '交通', 'desc' => '描述') AS $key => $val){
			if($key == 'desc' && empty($poi['desc'])){
				$wiki = get_wiki_content($poi['name']);
				$poi['desc'] = trim($wiki['content']);
			}
			if(!empty($poi[$key])){
				$tmp = utf8_str_cut_off(trim(strip_tags($poi[$key])), 50);
				if($key == 'name'){
					$tmp = '<a href="/search/poi/'.$poi['_id'].'" target="_blank">'.$tmp.'</a>';
				}
				$content .= <<<HTML
				<tr>
					<td style="font-weight:bold;vertical-align:top;width:11%;">
						$val:&nbsp;
					</td>
					<td style="width:89%;">
						$tmp
					</td>
				</tr>
HTML;
			}
		}
		return '<table style="width:300px;table-layout:fixed;">'.$content.'</table>';
	}
	/**
	 * 通过regionid与poi_id获取面包屑
	 * @param string $region_id
	 * @param string $page_category 页面分类属性
	 * @param string $poi_id
	 */
	private function get_crumbs($region_id, $page_category = null, $poi_id = null){
		//init
		$curmbs = array();
		$region = $this->mongo_db->Region_fetch_by__id((string)$region_id);
		$region_config = $this->config->item('region');
		$region_category = $region_config['category'];
		//第一级面包屑，region分类
		$crumbs[] = $region_category[$region['category']];
		//第二级面包屑，region地域
		!empty($region['area']) && $crumbs[] = $region['area'];
		//第三级面包屑，region名称
		!empty($region['name']) && $crumbs[] = $region['name'];
		if(!empty($poi_id)){
			$poi = $this->mongo_db->POI_fetch_by__id((string)$poi_id);
			//第五级面包屑，poi名称
			$crumbs[] = $poi['name'];
		}
		$page_category_config = $this->config->item('search_category');
		//最末一级，页面分类属性
		!empty($page_category) && !empty($page_category_config[$page_category]) && $crumbs[] = $page_category_config[$page_category];
		foreach($crumbs AS $key => $value){
			$value = trim($value);
			if(empty($value)) {
				unset($crumbs[$key]);
			}
		}
		return implode(" / ", $crumbs);
	}
	
	
	public function wiki_preview($name)
	{
		$name = rawurldecode($name);
		$wiki = get_wiki_content($name);
		$param = array(
			'title' => $name,
			'content' => $wiki['content'],
		);
		template('article_note', $param);
	}
	
	public function article_preview($id)
	{
		$id = rawurldecode($id);
		$article = $this->page_db->Article_fetch_by__id($id);
		$param = array(
			'title' => $article['title'],
			'content' => $article['content'],
		);
		template('article_note', $param);
	}
}














