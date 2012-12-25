<?php
class Users_handle {

	//CI控制器
	private $ci;
	//cookie信息
	public $cookie;
	//用户信息
	public $user_detail;
	
	/**
	 * function __construct
	 */
	public function __construct()
	{
		$this->ci = &get_instance();
		$this->ci->load->helper('common');
		$this->ci->load->library('weibo');
		$this->ci->load->library('mongo_db', array('dbname' => 'tripfm'));
		$this->init();
		$this->debug();
	}
	/**
	 * 初始化cookie
	 */
	private function init_cookie()
	{
		//去除cookie前缀
		$prelength = strlen($this->ci->config->item('cookie_prefix'));
		foreach($_COOKIE as $key => $val) {
			if(substr($key, 0, $prelength) == $this->ci->config->item('cookie_prefix')) {
				$this->cookie[(substr($key, $prelength))] = saddslashes($val);
			}
		}
	}
	/**
	 * 初始化信息
	 */
	private function init()
	{
		//初始化cookie
		$this->init_cookie();
    	//初始化用户信息
	    $this->user_detail = array();
	    
	    //从cookie读取信息，并验证
	    $co_info = array();
	    !empty($this->cookie['user_id']) && $co_info['user_id'] = $this->cookie['user_id'];
	    !empty($this->cookie['auth']) && $co_info['auth'] = $this->cookie['auth'];
	    
	    //验证auth
	    if( !empty($co_info['auth']) && $co_info['user_id'] )
	    {
	        /* 验证通过 */
	        if( $this->cookie_auth($co_info['user_id']) == $co_info['auth'] )
	        {
//	        	$this->ci->mongo_db->selectDB('tripfm');
	        	
	            $user = $this->ci->mongo_db->Users_fetch_by_user_id($co_info['user_id']);
	            
	            $weibo_config = $this->ci->config->item('weibo');
	            
				$wb_client = new WeiboClient($weibo_config['appkey'], $weibo_config['appsecret'], $user['oauth_token'], $user['oauth_token_secret']);
	            
	            $user_info = $wb_client->show_user($user['user_id']);
	            
	            if( !empty($user) ) {
	            	$this->user_detail = array(
	            		'user_id' 				=> $user['user_id'],
	            		'user_name'				=> $user_info['name'],
	            		'user_gender'			=> $user_info['gender'],
	            		'user_location'			=> $user_info['location'],
	            		'user_desc'				=> $user_info['description'],
	            		'user_img'				=> $user_info['profile_image_url'],
	            		'user_collection_count' => $this->ci->mongo_db->mongodb->UserCollection->find(array('user_id' => $co_info['user_id']))->count(),
	            	);
	            	return true;
	            }
	            return false;
	        }
	    }
	    
	    /* 没有验证通过，但存在用户信息，则进行信息清理 */
	    if( !empty($co_info['auth']) || !empty($co_info['user_id']) )
	    {
	        //clear cookie
	        $this->clear_cookie_user_info();
	        return false;
	    }
	    return false;
	}
	
	function debug()
	{
		//cookie 过期时间
	    $expire_time = $this->ci->config->item('cookie_expire');
		$get = $this->ci->input->get();
		//solr_debug
		if(@$get['solr_debug'] == 1){
			$this->ssetcookie('solr_debug', 1, $expire_time);
		}elseif(isset($get['solr_debug']) && $get['solr_debug'] == 0){
			$this->ssetcookie('solr_debug', '', -86400*365);
		}
		//mongo_debug
		if(@$get['mongo_debug'] == 1){
			$this->ssetcookie('mongo_debug', 1, $expire_time);
		}elseif(isset($get['mongo_debug']) && $get['mongo_debug'] == 0){
			$this->ssetcookie('mongo_debug', '', -86400*365);
		}
	}
	/**
	 * 判断用户是否登录, !!必须在用户信息初始化后使用!!
	 * 
	 * @return boolean
	 */
	public function user_is_login()
	{
	    if( !empty($this->user_detail) ) {
	        return true;
	    }
	    
	    return false;
	}
	/**
	 * 写登录cookie信息
	 * @param $user_info
	 */
	public function write_cookie_user_info($user_info, $remember_me=0)
	{
	    //cookie 过期时间
	    $expire_time = $remember_me ? $this->ci->config->item('cookie_expire') : 0;
	    
	    $this->ssetcookie('auth', $this->cookie_auth($user_info['user_id']), $expire_time);
	    $this->ssetcookie('user_id', $user_info['user_id'], $expire_time);
	}
	/**
	 * 清理登录cookie信息
	 */
	public function clear_cookie_user_info()
	{
	    $this->ssetcookie('auth', '', -86400*365);
	    $this->ssetcookie('user_id', '', -86400*365);
	}
	/**
	 * 生成cookie验证信息
	 * 
	 * @param $var
	 * @param bool $allow_multi_login 允许多IP登陆
	 */
	private function cookie_auth($var, $allow_multi_login=1)
	{
	    //允许多IP登陆
	    if( $allow_multi_login ) {
	        return md5($var."j9aqjkIWF_)23++==Dw`~W#c");
	    }
	    
	    return md5($var."j9aqjkIWF_)23++==Dw`~W#c".get_onlineip()); 
	}
	/**
	 * 设置cookie
	 * @param string $key
	 * @param string $value
	 * @param int $life
	 */
	public function ssetcookie($key, $value, $life = 0) {
		setcookie(
			$this->ci->config->item('cookie_prefix').$key, 
			$value, 
			$life ? (time()+$life) : 0, 
			$this->ci->config->item('cookie_path'), 
			$this->ci->config->item('cookie_domain'), 
			$_SERVER['SERVER_PORT'] == 443 ? 1 : 0
		);
	}
}




















