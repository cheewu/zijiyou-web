<?php

class Manage extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function index($category = null)
	{
		template("manage_search", array());
	}
	
	public function search($keyword) {
		$get = $this->input->get();
		if(empty($get)){
			$param = array(
				'hidden_header_nav' => TRUE,
			);
			template('index', $param);
			return;
		}
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
				$tmp = $this->mongo_db->Article_fetch_by__id($vlaue['_id'], $show_field);
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

		$total_cnt = $reponse['response']['numFound'];
		$pg = $get['pg'] ?: 1;
		$title = trim($get['q']);
		$title .= trim($get['ct']) ?: '';
		//拼接url
		$url = "/manage/search/?q={$get['q']}";
		$param = array(
			'category' => $category,
			'result' => $result,
			'relative_keyword' => $keyword_relat_res,
			'pg' => $pg,
			'title' => $title,
			'get' => recursive_rawurldecode($get),

			'multi' => multi($total_cnt, $solr_config['solr_default_ps'], $pg, $url),
		);
		template('manage_article_note', $param);		
	}
	
	public function delete($_id) {
		$delete_url = sprintf("?user=admin&op=del&keys=%s", $_id);

		$this->solr_request->init();

		$this->solr_request->append_request_url($delete_url);
		$reponse = shttp_request($this->solr_request->get_url());
		
		$update = array('_id'=>$_id, 'status'=>-99);
		$result = $this->mongo_db->Article_update($update);
		if($result) {
			echo '删除成功';
		} else {
			echo '删除失败';
		}
	}
}
