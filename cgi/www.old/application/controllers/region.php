<?php

class Region extends CI_Controller {
	
	private $region_conf;
	
	public function __construct() {
		parent::__construct();
		$this->region_conf = $this->config->item('region');
	}
	
	public function index()
	{
		$this->lists();
	}
	
	public function lists($pg = 1)
	{
		$get = $this->input->get();
		
		$get_query = array();
		
//		$query = array('area' => array('$exists' => true));
		$query = array('is_del' => false);
		if(!empty($get['category']) && $get['category'] != 'all'){
			if($get['category'] == 'deleted'){
				$query['is_del'] = true;
			}else{
				$query['category'] = $get['category'];
			}
			$get_query[] = 'category='.$get['category'];
		}else{
			$get['category'] = 'all';
		}
		if(!empty($get['q'])){
			$query['name'] = array('$regex' => $get['q']);
			$get_query[] = 'q='.$get['q'];
		}
		
		$ps = $this->region_conf['default_ps'];
		
		$result = $this->mongo_db->Region_fetchall($query, null, array('name' => -1), $ps, $pg );
//		pr($result);
		$total_pg_float = $result['count']/$ps;
		$total_pg_int = intval($total_pg_float);
		$total_pg = ($total_pg_float - $total_pg_int) > 0 ? $total_pg_int + 1 : $total_pg_int;
		$category = $this->mongo_db->Region_distinct_by_category();
		
		$get_url = implode("&", $get_query);
		$append_url = $get_url ? '?'.$get_url : '';
		
		if($pg<=1){
			$prev = 'prev-disabled';
			$prev_url = '#';
		}else{
			$prev = 'prev';
			$prev_url = "/region/lists/".($pg-1).$append_url;
		}
		if($pg>=$total_pg){
			$next = 'next-disabled';
			$next_url = '#';
		}else{
			$next = 'next';
			$next_url = "/region/lists/".($pg+1).$append_url;
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
			'category_cn' => $this->region_conf['category'],
		);
		
		$this->load->view('region_list.php', $param);
	}
	
	public function item($id)
	{
		$get = $this->input->get();
		
		$append_url = isset($get['q']) ? "?q={$get['q']}" : "";
		
		$result = $this->mongo_db->Region_fetch_by__id($id);
		$param = array(
			'result' => recursive_trim($result),
			'pg' => $get['pg'] ?: 1,
			'append_url' => $append_url,
			'category' => $this->region_conf['category'],
		);
		
		$this->load->view('region_item.php', $param);
	}
	
	public function save()
	{
		$post = $this->input->post();
//		pr($post);
//		if($post['places'] != $post['address_input']){
//			echo '<script type="text/javascript">alert("情确定你输入的地点能够在google map中找到")</script>';
//			header("Location: /region/item/{$post['id']}?pg={$pg}");
//			$this->item($post['_id']);
//			return;		
//		}
		$pg = $post['pg'];
		
		$update = array(
			'_id' => $post['_id'],
			'center' => array(floatval($post['lat']), floatval($post['lng'])),
			'area' => $post['area'],
			'name' => $post['name'],
			'keyword' => $post['keyword'],
			'website' => $post['website'],
			'timezone' => $post['timezone'],
			'blog' => $post['blog'],
			'weibo' => $post['weibo'],
			'desc' => $post['desc'],
			'is_important' => $post['is_important'] ? true : false,
//			'istravel' => $post['istravel'],
			'category' => $post['category'],
			'englishName' => $post['englishName']
		);
//		pr($update, 0);
		$result = $this->mongo_db->Region_update($update);
		if(!$result){
			echo '<script type="text/javascript">alert("修改失败请联系管理员")</script>';
			$this->item($post['_id']);
		}else{
			header("Location: /region/item/{$post['_id']}?pg={$pg}");
		}
				
	}
	
	public function delete()
	{
		$get = $this->input->get();
		$update = array(
			'_id' => $get['id'],
			'is_del' => true,	
		);
		$result = $this->mongo_db->Region_update($update);
		if($result){
			echo 'success';
		}else{
			echo 'fail';
		}
		
	}
}














