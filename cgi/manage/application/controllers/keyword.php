<?php

class Keyword extends CI_Controller {
	public function __construct() {
		parent::__construct();
		
		$this->load->library('Mongo_db');
		$this->config->load('config_run');
		$this->load->helper('common');
		$this->mongo_db->selectDB('tripfm');
	}
	
	public function index()
	{
		$this->lists();
	}
	
	public function lists($selected_category = null, $selected_char = null)
	{
		if(empty($selected_category)){
			$selected_category = 'city';
		}
		if(empty($selected_char)){
			$selected_char = 'A';
		}
		$language = $this->input->get('lan');
		$length = $this->input->get('len');
		$type = $this->input->get('type');
		$pg = $this->input->get('pg');
		$pg = !empty($pg) ? $pg : 1;
		$ps = $this->config->item('default_ps') ?: 100;
		$category = $this->mongo_db->Keyword_distinct_by_category();
		$count_array = $this->mongo_db->Keyword_distinct_by_count();
		$query = array(
			'category' => $selected_category, 
			'firstchar' => $selected_char,
			'is_del' => false,
		);
		if(!empty($length)){
			if($length == 'gt10'){
				$query['count'] = array('$gt' => 10);
			}else{
				$query['count'] = intval($length);
			}
			$append_url = 'len='.$length;
		}
		if(in_array($language, array('cn', 'en', 'mix'))){
			$query['type'] = $language;
			if($query['type'] == 'mix'){
				$query['count'] = -1;
			}
		}else{
			$language = 'all';
		}
		$append_url .= '&lan='.$language;
		if($selected_category == 'del'){
			unset($query['category']);
			$query['is_del'] = true;
			$type = 'del';
		}
		if($selected_char == 'all'){
			unset($query['firstchar']);
		}
//		pr($query);
		$result = $this->mongo_db->Keyword_fetchall($query, null, array('tf' => -1), $ps, $pg );
		
//		$result_filter = $final_result = array();
//		
//		foreach($result['item'] AS $value){
//			if(get_first_pinyin_char($value['keyword']) == $selected_char){
//				$result_filter[] = $value;
//			}
//		}
		
		$total_pg_float = $result['count']/$ps;
		$total_pg_int = intval($total_pg_float);
		$total_pg = ($total_pg_float - $total_pg_int) > 0 ? $total_pg_int + 1 : $total_pg_int;
		
//		foreach($result_filter AS $key => $value){
//			if($key >= ($pg-1) * $ps && $key < $pg * $ps){
//				$final_result[] = $value;
//			}
//		}
		if($pg<=1){
			$prev = 'prev-disabled';
			$prev_url = '#';
		}else{
			$prev = 'prev';
			$prev_url = "/keyword/lists/{$selected_category}/{$selected_char}/?{$append_url}&pg=".($pg-1);
		}
		if($pg>=$total_pg){
			$next = 'next-disabled';
			$next_url = '#';
		}else{
			$next = 'next';
			$next_url = "/keyword/lists/{$selected_category}/{$selected_char}/?{$append_url}&pg=".($pg+1);
		}
		$param = array(
			'category' => $category,
			'selected_category' => $selected_category,
			'selected_char' => $selected_char,
			'selected_cnt' => $length,
			'selected_lan' => $language,
			'pg' => $pg,
			'prev_url' => $prev_url,
			'next_url' => $next_url,
			'prev' => $prev,
			'next' => $next,
			'total_pg' => $total_pg,
			'result' => $result['item'],
			'type' => $type,
			'append_url' => $append_url, 
		);
		$this->load->view('keyword_list.php', $param);
	}
	
	public function modify($id = null)
	{
		$result = $this->mongo_db->Keyword_fetch_by__id($id);
		$category = $this->mongo_db->Keyword_distinct_by_category();
		$title = 'Modified';
//		pr($result);
		$param = array(
			'result' => $result,
			'category' => $category,
			'title' => $title,
		);
		$this->load->view('keyword_modify.php',$param);
	}
	
	public function modified_ajax()
	{
		$category = $this->input->get('category');
		$keyword = trim($this->input->get('keyword'));
		$id = $this->input->get('id');
		if(!empty($id)){
			$result = $this->mongo_db->Keyword_fetch_by__id($id);
			if($result['category'] == $category && $result['keyword'] == $keyword){
				echo 'nochange';
				return;
			}else{
				$update = array(
					'category' => $category,
					'keyword' => $keyword,
					'_id' => $id,
				);
			$result = $this->mongo_db->Keyword_update($update);
			}
		}else{
			$len_lan = get_keyword_len_lan($keyword);
			$add = array(
				'category' => $category,
				'keyword' => $keyword,
				'firstchar' => get_first_pinyin_char($keyword),
				'tf' => 0,
				'is_del' => false,
				'count' => $len_lan['count'],
				'type' => $len_lan['type'],
			);
			$result = $this->mongo_db->Keyword_add($add);
		}
		if($result){
			echo 'success';
		}else{
			echo false;
		}
	}
	
	public function remove_ajax()
	{
		$id = $this->input->get('id');
		
		$update = array(
			'_id' => $id,
			'is_del' => true,
		);
		$result = $this->mongo_db->Keyword_update($update);
//		$result = $this->mongo_db->Keyword_delete_by__id($id);
		if($result){
			echo 'success';
		}else{
			echo false;
		}
	}
}














