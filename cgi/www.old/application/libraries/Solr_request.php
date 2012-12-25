<?php
class Solr_request{
	//存储配置信息
	private $config;
	//查询词关联数组
	private $q;
	//查询url
	private $url;
	//ci 参数
	private $ci;
	/**
	 * __construct()
	 */
	public function __construct()
	{
		$this->ci = &get_instance();
		$this->config = $this->ci->config->item('solr');
	}
	
	public function get_url() {
		return $this->url;
	}
	/**
	 * 初始化变量
	 */
	public function init()
	{
		$this->url = $this->config['solr_url'].':'.$this->config['solr_port'];
		$this->append_request_url($this->config['solr_url_suffix']);
	}
	/**
	 * 将请求参数解析进$q
	 * @param array() $extra_options 除了get之外的附加参数
	 * @param array() $url_parse_sets 默认为get参数
	 */
	public function parse_request($extra_options = array(), $url_parse_sets = null)
	{
		if(is_null($url_parse_sets)){
			$url_parse_sets = $this->ci->input->get() ?: array();
		}
		if(!empty($extra_options)){
			$url_parse_sets = array_merge($url_parse_sets, $extra_options);
		}
		//分页
		$url_parse_sets['pg'] = $url_parse_sets['pg'] ?: 1;
		$this->q['rows'] = $url_parse_sets['ps'] ?: $this->config['solr_default_ps'];
		$this->q['start'] = ($url_parse_sets['pg'] - 1) * $this->q['rows'];
		//查询词
		!empty($url_parse_sets['q']) && $this->q['q'] = $url_parse_sets['q'];
		!empty($url_parse_sets['dr']) && $this->q['dr'] = $url_parse_sets['dr'];
		!empty($url_parse_sets['fdr']) && $this->q['fdr'] = $url_parse_sets['fdr'];
		!empty($url_parse_sets['ct']) && $this->q['zct'] = $url_parse_sets['ct'];
		if(!empty($url_parse_sets['weibo']) && $url_parse_sets['weibo']){
			//微博客
			$this->append_request_url($this->config['solr_url_group']['weibo']);
		}else{
			//复合查询接口
			$this->append_request_url($this->config['solr_url_group']['multi']);
		}
		
		
		
		/*
		//ct字段为查询词的分类 只有note有分类
		if(isset($url_parse_sets['ct'])){
			!empty($url_parse_sets['ct']) && $this->q['zct'] = $url_parse_sets['ct'];
			$this->append_request_url($this->config['solr_url_group']['note']);
		//微博
		}elseif(!empty($url_parse_sets['weibo']) && $url_parse_sets['weibo']){
			$this->append_request_url($this->config['solr_url_group']['weibo']);
		//article
		}else{
//			$this->append_request_url($this->config['solr_url_group']['article']);
			$this->append_request_url($this->config['solr_url_group']['multi']);
		}
		*/
	}
	/**
	 * 发起请求
	 * @return array() 返回查询结果
	 */
	public function do_request()
	{
		//生成查询url
		$this->generate_request_url();
		//pr($this->url);
		//发起请求
		$request_res = shttp_request($this->url, array('timeout' => 5));
		//过滤破损字符
		$request_res = iconv("utf-8", "utf-8", $request_res);
		//解析xml
		$simple_xml = simplexml_load_string($request_res);
		$res = xml_2_arr($simple_xml, array('doc'));
//		pr($res);
		return $res;
	}
	/**
	 * 生成查询url
	 */
	private function generate_request_url()
	{
		//拼接solr默认路径
		if(!empty($this->config['solr_query_type'])){
//			if(!empty($this->q['and']) || !empty($this->q['or'])){
//				$this->append_request_url($this->config['solr_query_type']['multi']);
//			}else{
				$this->append_request_url($this->config['solr_query_type']['ct']);
//			}
		}
		//拼接默认请求
		$this->append_request_url('');//补齐末尾 '/'
		$parse_set = array(); 
		foreach(array_merge($this->config['solr_basic_query'], $this->q) AS $key => $value){
			$value = rawurlencode($value);
			$parse_set[] = "{$key}={$value}";
		}
		$this->append_request_url(implode("&", $parse_set),'?');
	}
	/**
	 * 拼凑查询url
	 * @param 待拼接url $append_url
	 * @param 拼接字符 $implode_char
	 */
	public function append_request_url($append_url, $implode_char = '/')
	{
		if(substr($this->url, -1) == $implode_char || substr($append_url, 0, 1) == $implode_char){
			$this->url .= $append_url;
		}else{
			$this->url .= $implode_char.$append_url;
		}
	}
}