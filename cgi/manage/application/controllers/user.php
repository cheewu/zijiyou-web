<?php
class User extends CI_Controller {
	
	//weibo config
	private $weibo_config;
	
	public function __construct() {
		parent::__construct();
		$this->weibo_config = $this->config->item('weibo');
	}
	
	public function login()
	{
		$coming_url = $_SERVER['HTTP_REFERER'];
		
		$wb_oauth = new Weibooauth($this->weibo_config['appkey'], $this->weibo_config['appsecret']);
		
		$keys = $wb_oauth->getRequestToken();
		
		$callback_url = "http://".$_SERVER['HTTP_HOST']."/user/wb_callback";
		
		$callback_url .= '?r_oauth_token='.rawurlencode($keys['oauth_token']).'&r_oauth_token_secret='.rawurlencode($keys['oauth_token_secret']).'&redict_url='.rawurlencode($coming_url);
	
		$oauth_url = $wb_oauth->getAuthorizeURL($keys['oauth_token'] ,false , $callback_url);
		
		$param = array(
			'oauth_url' => $oauth_url, 
		);
		
//		$this->load->view('user_login.php', $param);
		template('user_login', $param);
	}
	
	public function wb_callback()
	{
		$weibo_config = $this->config->item('weibo');
		// argvs
		$oauth_token         = trim($_GET['r_oauth_token']);
		$oauth_token_secret  = trim($_GET['r_oauth_token_secret']);
		$oauth_verifier      = trim($_GET['oauth_verifier']);
		if( empty($oauth_token) || empty($oauth_token_secret) ) {
			echo "oauth_token or oauth_token_secret is EMPTY\n";exit;
		}
		if( empty($oauth_verifier) ) {
			echo "oauth_verifier is EMPTY\n";exit;
		}
		
		// WeiboOAuth
		$wb_oauth = new Weibooauth($this->weibo_config['appkey'], $this->weibo_config['appsecret'], $oauth_token, $oauth_token_secret);
		// 用户的OAuth验证串
		$user_access_keys = $wb_oauth->getAccessToken($oauth_verifier);
		if( empty($user_access_keys['user_id']) ) {
			echo "'user_id' in getAccessToken is empty\n";
			exit;
		}
		
		//如果用户存在则刷新token，不存在则插入
		$users = $this->mongo_db->Users_fetch_by_user_id($user_access_keys['user_id']);
		if(!empty($users)){
			$user_id = $user_access_keys['user_id'];
			unset($user_access_keys['user_id']);
			$this->mongo_db->Users_update($user_access_keys, array('user_id' => $user_id));
		}else{
			$this->mongo_db->Users_add(array_merge($user_access_keys, array('first_login_date' => date('Y-m-d H:i:s'))));
		}
		
		$this->users_handle->write_cookie_user_info(array('user_id' => $user_access_keys['user_id'] ?: $users['user_id']), 1);
		
		
		
		header('Location: '.($_GET['redict_url'] ?: '/') );
	}
	
	public function collection()
	{
		if(!$this->users_handle->user_is_login()){
			header("Location: /user/login");
		}
/*		$get = $this->input->get();
		//单个收藏内容
		$item_detail = array();
		//收藏结果集
		$collect_res = array();
		//marker 数据
		$marker = array();
		//用户基本信息
		$user_info = $this->users_handle->user_detail;
		$user_collection = $this->mongo_db->UserCollection_fetchall_by_user_id($user_info['user_id']);
		foreach($user_collection['item'] AS $key => $value){
			unset($value['_id']);
			if($value['category'] == 'Article' || $value['category'] == 'Note'){
//				$this->mongo_db->selectDB('spiderV21');
				$func_name = $value['category'].'_fetch_by__id';
			}else{
//				$this->mongo_db->selectDB('tripfm');
				$func_name = $value['category'].'_fetch_by__id';
			}
			$item_detail = $this->mongo_db->$func_name($value['cid']);
			
			if(empty($get)){
				$collect_res[$value['category']][] = array_merge($item_detail, $value);
				if($value['category'] == 'POI' || $value['category'] == 'Region'){
					$config = $this->config->item(strtolower($value['category']));
					$map_marker_desc = '';
					foreach($config['dbfield'] AS $k => $v){
						$map_marker_desc .= !empty($item_detail[$k]) ? '<b>'.$v.'</b>:'.google_map_strip_char(strip_tags($value[$k] ?: $item_detail[$k])).'</br>' : '';
					}
					$marker[$value['cid']] = array(
						'title' => !empty($item_detail['area']) && !empty($item_detail['name']) ? $item_detail['area'].':'.$item_detail['name'] : '',
						'address' => $item_detail['name'],
						'position' => !empty($value['center']) ? array('lt' => $value['center'][0], 'lg' => $value['center'][1]) : null,
						'content' => $map_marker_desc ?: '没有介绍',
					);
				}
			}else{
				$collect_res[] = array_merge($item_detail, $value);
			}
		}
		*/
		$param = array(
			'collect_res' => $collect_res,
			'marker' => $marker,
			'hidden_header_nav' => TRUE,
		);
		if(empty($get)){
//			$this->load->view('user_collection.php', $param);
			template('user_collection', $param);
		}else{
			$this->load->view('user_collection_print.php', $param);
		}
		
	}
	
	public function creat_collection()
	{
		if(!$this->users_handle->user_is_login() || !$this->input->post('collect_type')){
			header("Location: /user/login");
		}
		$post = $this->input->post();
		$type_options = array(
			'note' => 'Note',
			'arti' => 'Article',
			'regi' => 'Region',
			'poi' => 'POI',
		);
		$type = $type_options[$post['collect_type']] ?: $post['collect_type'];
		if($type == 'Region' || $type == 'POI'){
			
			$config = $this->config->item(strtolower($type));
			
//			$this->mongo_db->selectDB('tripfm');
			
			$func_name = $type.'_fetch_by__id';
			
			$orign_detail = $this->mongo_db->$func_name($post['_id']);
			
			$orign_detail = recursive_trim($orign_detail);
			
			$add = array();
			
			foreach($config['dbfield'] AS $key => $value){
				if($key == 'desc'){$orign_detail[$key] = strip_tags($orign_detail[$key]);}
				if($post[$key] != $orign_detail[$key]){
					$add[$key] = $post[$key];
				}
			}
			
			$append_field = array(	
				'cid' => $post['_id'],
				'center' => array($post['lat'], $post['lng']),
				'category' => $type ?: 'undefined',
				'collect_time' => date('Y-m-d H:i:s'),
				'user_id' => $this->users_handle->user_detail['user_id'],
				'user_nick' => $this->users_handle->user_detail['user_name'],
			);
			
			$add = array_merge($add, $append_field);
			
			$this->mongo_db->UserCollection_add($add);
			
			$this->load->view('user_collection_success.php');
		}
		if($type == 'Article' || $type == 'Note'){
			
			$this->mongo_db->selectDB('spiderV21');
			
			$func_name = $type.'_fetch_by__id';
			
			$orign_detail = $this->mongo_db->$func_name($post['_id']);
			
			$add = array();
			
			foreach(array('title', 'content') AS $value){
				if($post[$value] != $orign_detail[$value]){
					$add[$value] = $post[$value];
				}
			}
			
			$append_field = array(	
				'cid' => $post['_id'],
				'category' => $type ?: 'undefined',
				'collect_time' => date('Y-m-d H:i:s'),
				'user_id' => $this->users_handle->user_detail['user_id'],
				'user_nick' => $this->users_handle->user_detail['user_name'],
			);
			$add = array_merge($add, $append_field);
			
//			$this->mongo_db->selectDB('tripfm');
			
			$this->mongo_db->UserCollection_add($add);
			
			$this->load->view('user_collection_success.php');
		}
		
	}
	
	public function ajax_collection_check()
	{
		if(!$this->users_handle->user_is_login()){
			echo 'notlogin';
			return;
		}
		$get = $this->input->get();
		$id = $get['id'];
		$type = $get['cate'];
		//collection cate对应关系
		$type_options = array(
			'note' => 'Note',
			'arti' => 'Article',
			'regi' => 'Region',
			'poi' => 'POI',
		);
//		$this->mongo_db->selectDB('tripfm');
		$exist_check = $this->mongo_db->UserCollection_fetch(array('cid' => $id, 'user_id' => $this->users_handle->user_detail['user_id']));
		if(!empty($exist_check)){
			echo 'collected';
			return;
		}else{
			echo 'none';
			return;
		}
	}
	
	public function ajax_creat_collection()
	{
		if(!$this->users_handle->user_is_login()){
			echo 'notlogin';
			return;
		}
		$get = $this->input->get();
		$id = $get['id'];
		$type = $get['cate'];
		//collection cate对应关系
		$type_options = array(
			'note' => 'Note',
			'arti' => 'Article',
			'regi' => 'Region',
			'poi' => 'POI',
		);
		$exist_check = $this->mongo_db->UserCollection_fetch(array('cid' => $id, 'user_id' => $this->users_handle->user_detail['user_id']));
		if(!empty($exist_check)){
			echo 'collected';
			return;
		}
		$add = array(
			'cid' => $id,
			'category' => $type_options[$type] ?: 'undefined',
			'collect_time' => date('Y-m-d H:i:s'),
			'user_id' => $this->users_handle->user_detail['user_id'],
			'user_nick' => $this->users_handle->user_detail['user_name'],
		);
		if($this->mongo_db->UserCollection_add($add)){
			echo 'success';
			return;
		}else{
			echo 'faild';
			return;
		}
	}
	
}