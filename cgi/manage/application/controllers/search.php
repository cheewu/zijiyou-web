<?php
class Search extends CI_Controller {
	
	public $query;
	
	public $search_config;
	
	public $res;
	
	public $mongo_query = array();
	
	public function __construct() {
		parent::__construct();
		$this->search_config = $this->config->item('search');
		$this->query = $this->search_config['query_default'];
		$this->mongo_query = $this->search_config['mongo_query_default'];
	}
	
	public function index() {
		$this->title = 'zijiyou管理后台';
		$this->load->view('index_main');
	}
	
	public function lists() {
		$this->query = array_merge($this->query, array_filter($this->input->get(), 'trim'));
		$res = $this->do_search();
		$list_key = strtolower($this->query['collection']).'_field';
		$tmp_config = $this->config->item($list_key);
		$list_field = $tmp_config['list'];
		$param = array(
			'fields' 	=> $list_field,
			'lists'  	=> $this->res['item'],
		);
		
		$this->title = $this->query['collection'].' 搜索';
		$this->load->view('lists', $param);
	}
	
	private function do_search() {
		$func = $this->query['collection'].'_handle';
		$this->order = !empty($this->search_config['order'][$this->query['collection']]) ? $this->search_config['order'][$this->query['collection']] : array();
		!empty($this->query['category']) && $this->mongo_query['category'] = $this->query['category'];
		$this->$func();
		$func_nav = $this->query['collection'].'_distinct_by_category';
		$this->res['nav'] = $this->mongo_db->$func_nav();
	}
	
	private function Region_handle() {
	    $search_area = 'area';
	    if($this->query['search_field']) { 
	        $search_area = $this->query['search_field'];
	        unset($this->query['search_field']);
	    }
		$this->_parse_query(array('q' => array('$regex' => $search_area)));
		$this->res = $this->mongo_db->Region_fetchall($this->mongo_query, null, $this->order, $this->query['ps'], $this->query['pg']);
		foreach($this->res['item'] AS $key => $value) {
			$cnt = $this->mongo_db->mongodb->POI->find(array('regionId' => new MongoID($value['_id'])))->count();
			$this->res['item'][$key]['poi_cnt'] = $cnt;
		}
	}
	
	private function POI_handle() {
		$this->_parse_query(array('q' => array('$regex' => 'name')));
		!empty($this->query['regionId']) && $this->mongo_query['regionId'] = new MongoID($this->query['regionId']);
		$this->res = $this->mongo_db->POI_fetchall($this->mongo_query, null, $this->order, $this->query['ps'], $this->query['pg']);
		foreach($this->res['item'] AS $key => $value) {
			if(!empty($value['regionId'])) {
				$region = $this->mongo_db->Region_fetch_by__id($value['regionId']);
				!empty($region['name']) && $this->res['item'][$key]['area'] = $region['name'];
			}
		}
	}
	
	private function Article_handle() {
		$this->_parse_query(array('q' => array('$regex' => 'title')));
		//pr($this->mongo_query);
		$this->res = $this->mongo_db->Article_fetchall($this->mongo_query, null, $this->order, $this->query['ps'], $this->query['pg']);
		foreach($this->res['item'] AS $key => $value) {
			$this->res['item'][$key]['content'] = utf8_str_cut_off(trim(strip_tags($this->res['item'][$key]['content'])), 50);
		}
	}
	
	private function Wikipedia_handle() {
		$this->_parse_query(array('q' => array('$regex' => 'title')));
		$this->res = $this->mongo_db->Wikipedia_fetchall($this->mongo_query, null, $this->order, $this->query['ps'], $this->query['pg']);
		foreach($this->res['item'] AS $key => $value) {
			$this->res['item'][$key]['content'] = utf8_str_cut_off(trim(strip_tags($this->res['item'][$key]['content'])), 50);
		}
	}
	
	//array('q' => 'title')
	private function _parse_query($get_query_to_mongo_query) {
		foreach($get_query_to_mongo_query AS $get_query => $mongo_query) {
			if(isset($this->query[$get_query]) && $this->query[$get_query] !== '') {
				if(is_array($mongo_query)) {
					list($type) = array_keys($mongo_query);
					$this->mongo_query[$mongo_query[$type]] = array($type => $this->query[$get_query]);
				} else {
					$this->mongo_query[$mongo_query] = $this->query[$get_query];
				}
			}
		}
	}
}
