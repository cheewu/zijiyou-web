<?php
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	class Poi extends CI_Controller {
		
		private $poi_conf;
		
		public function __construct() {
			parent::__construct();
			$this->load->library('mongo_db');
			$this->load->helper('common');
			$this->config->load('config_run');
			$this->mongo_db->selectDB('tripfm');
			$this->poi_conf = $this->config->item('poi');
		}
		
		public function index() {
			return $this->lists();
		}
		
		public function lists($pg=1) {
			
			$query = array();
			$append_url = '';
			
			$get = $this->input->get();
			
			$query = array('$or' => array(array('is_del' => array('$exists' => false)), array('is_del' => false)));
			if(!empty($get['category']) && $get['category'] != 'all'){
				if($get['category'] == 'none'){
					$query['category'] = array('$exists' => false);
				}elseif($get['category'] == 'deleted'){
					$query['is_del'] = true;
				}else{
					$query['category'] = $get['category'];
				}
				$get_query[] = 'category='.$get['category'];
			}else{
				$get['category'] = 'all';
			}
			if(!empty($get['q'])){
				$region_id_res = $this->mongo_db->Region_fetch_by_name($get['q']);
				$query['regionId'] = new MongoID($region_id_res['_id']);
				$get_query[] = 'q='.$get['q'];
			}
			$ps = $this->poi_conf['default_ps'];
			$result = $this->mongo_db->POI_fetchall($query, null, array('name' => -1), $this->poi_conf['default_ps'], $pg);
			
			$result = recursive_trim($result);
			
			$total_pg_float = $result['count']/$ps;
			$total_pg_int = intval($total_pg_float);
			$total_pg = ($total_pg_float - $total_pg_int) > 0 ? $total_pg_int + 1 : $total_pg_int;
			$category = $this->mongo_db->POI_distinct_by_category();
			
			foreach($result['item'] AS $key => $value){
				if(isset($value['regionId'])){
					$area_res = $this->mongo_db->Region_fetch_by__id($value['regionId']);
					$result['item'][$key]['area'] = $area_res['name'];
				}
			}
			
			$get_url = implode("&", $get_query);
			$append_url = $get_url ? '?'.$get_url : '';
			
			if($pg<=1){
				$prev = 'prev-disabled';
				$prev_url = '#';
			}else{
				$prev = 'prev';
				$prev_url = "/poi/lists/".($pg-1).$append_url;
			}
			if($pg>=$total_pg){
				$next = 'next-disabled';
				$next_url = '#';
			}else{
				$next = 'next';
				$next_url = "/poi/lists/".($pg+1).$append_url;
			}
			$param = array(
				'pg' => $pg,
				'get' => $get,
				'prev_url' => $prev_url,
				'next_url' => $next_url,
				'prev' => $prev,
				'next' => $next,
				'total_pg' => $total_pg,
				'result' => $result['item'],
				'category' => $category,
			);
//			pr($param);
			$this->load->view('poi_list.php', $param);			
		}
		
		public function item($id = null) {
			
			$get = $this->input->get();
			
			$append_url = isset($get['q']) ? "?q={$get['q']}" : "";
			
			if(!empty($id)){
				$result = $this->mongo_db->POI_fetch_by__id($id);
			}
			$result = recursive_trim($result);
			
			$poi_categories = $this->config->item('poi_category');
			
			if(isset($result['regionId'])){
				$area_res = $this->mongo_db->Region_fetch_by__id($result['regionId']);
				$result['area'] = $area_res['name'];
			}
			
			if(!empty($result)) {
				$params = array(
					'result' => $result,
					'pg' => $get['pg'],
					'append_url' => $append_url,
					'category' => $this->poi_conf['category'],
				);
			}
			$this->load->view('poi_item.php', $params);
			
		}
		
		
		public function save() {
			$post = $this->input->post();
//			pr($post);
//			if($post['address'] != $post['address_input']){
//				echo '<script type="text/javascript">alert("情确定你输入的地点能够在google map中找到")</script>';
//				header("Location: /region/item/{$post['id']}?pg={$pg}");
//				$this->item($post['_id']);
//				return;		
//			}
			$update_field = array(
				'_id', 'area', 'name', 'englishName', 'keyword', 
				'ticket', 'telNum', 'address', 'openTime', 'website',
				'weibo', 'blog', 'desc', 'category',
			);
			foreach($update_field AS $value){
				$update[$value] = trim($post[$value]);
			}
			$update['center'] = array(floatval($post['lat']), floatval($post['lng']));
			
			//name must start with city name
			$result = $this->mongo_db->POI_update($update);
			if(!$result){
				echo '<script type="text/javascript">alert("修改失败请联系管理员")</script>';
				$this->item($post['_id']);
			}else{
				header("Location: /poi/item/{$post['_id']}?pg={$post['pg']}");
			}
					
		}
		
		public function delete()
		{
			$get = $this->input->get();
			$update = array(
				'_id' => $get['id'],
				'is_del' => true,	
			);
			$result = $this->mongo_db->POI_update($update);
			if($result){
				echo 'success';
			}else{
				echo 'fail';
			}
			
		}
		
	}
	
