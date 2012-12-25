<?php
class Mongo_db{
	
	//connection
	public $con;
	//mongodb
	public $mongodb;
	//collection
	private $collection;
	//debug
	private $start_time;
	
	/**
	 * __construct() 读取配置文件，初始化
	 */
	public function __construct($param = array()) 
	{
		$ci = &get_instance();
//		$ci->load->helper('common');
//		$ci->config->load('config_run');
		$default_config = $ci->config->item('mongo');
		$host = @$param['host'] ?: $default_config['m_host'];
		$port = @$param['port'] ?: $default_config['m_port'];
		$dbname = @$param['dbname'] ?: $default_config['m_dbname'];
		$this->con = new Mongo("mongodb://{$host}:{$port}", array('username' => 'admin', 'password' => 'iamzijiyou'));
		$this->mongodb = $this->con->selectDB($dbname);
	}
	/**
	 * 切换数据库
	 * @param string $dbname
	 */
	public function selectDB($dbname)
	{
		if(!empty($dbname)){
//			$this->mongodb = $this->con->selectDB($dbname);
		}
	}
	/**
	 * 魔术函数查询数据库
	 * @param string $name
	 * @param array $arguments
	 */
	public function __call($name, $arguments)
	{
		global $_SGLOBAL;
		//记录debuginfo
		$this->start_time = microtime(true);
		
		/* check function is exist */
		if( function_exists($name) )
		{
			return call_user_func_array($name, $arguments);
		}
		
		/* __CALL */
		$matches = array();
		
		// * <collection>_fetch_by_<query_field>($query_val, $show_filed array($field1,$field2...))
		// * <collection>_fetch($query_field_and_value, $show_filed array($field1,$field2...))
		if( preg_match_all("/^(.*)_fetch_by_(.*)$/i", $name, $matches) || preg_match_all("/^(.*)_fetch$/i", $name, $matches) )
		{
			$collection_name = $matches[1][0];
			$this->collection = $this->mongodb->$collection_name;
			
			if(isset($matches[2])){
				$query_field_and_value = array($matches[2][0] => $arguments[0]);
			}else{
				$query_field_and_value = $arguments[0];
			}
			
			$show_filed = array();
			//转换id为 mongoid对象
			if(isset($query_field_and_value['_id']) && !empty($query_field_and_value['_id']) && !is_array($query_field_and_value['_id'])){
				$query_field_and_value['_id'] = new MongoId($query_field_and_value['_id']);
			}
			if(isset($query_field_and_value['_id_noo'])) {
				$query_field_and_value['_id'] = $query_field_and_value['_id_noo'];
				unset($query_field_and_value['_id_noo']);
			}
			if(!empty($arguments[1])){
				foreach($arguments[1] AS $val){
					$show_filed[$val] = 1; 
				}
				$res = $this->collection->findOne($query_field_and_value, $show_filed);
			}else{
				$res = $this->collection->findOne($query_field_and_value);
			}
			//debug
			$debug_mongo_query = array(
				'query_field_and_value' => $query_field_and_value,
				'show_filed' => $show_filed,
			);
			$_SGLOBAL['debug_info']['mongo'][] = array('mongo_query' => $debug_mongo_query, 'time_cost' => (microtime(true) - $this->start_time));
			return $this->objectid_to_string($res);
		}
		
		// * <table>_fetchall_by_<query_field>($query_value, $show_filed=null, $orderby=null, $pagesize=null, $page=null)
		// * <table>_fetchall($query_field_and_value, $show_filed=null, $orderby=null, $pagesize=null, $page=null)
		if( preg_match_all("/^(.*)_fetchall_by_(.*)$/i", $name, $matches) || preg_match_all("/^(.*)_fetchall$/i", $name, $matches)  )
		{
			$result = array();
			$collection_name = $matches[1][0];
			$this->collection = $this->mongodb->$collection_name;
			
			if(isset($matches[2])){
				$query_field_and_value = array($matches[2][0] => $arguments[0]);
			}else{
				$query_field_and_value = $arguments[0];
			}
			
			$param = array(
				'query_field_and_value' => $query_field_and_value,  //查询字段 $query_field_and_value array($field1 => $val1, $field2 => $val2...)
				'show_field' => !empty($arguments[1]) ? $arguments[1] : null,  //显示字段 $show_field array($field1,field2...)
				'order_by' => !empty($arguments[2]) ? $arguments[2] : null, //排序方式 $orderby array($field => $type) $type 中 1为从小到大 -1为从大到小
				'pagesize' => !empty($arguments[3]) ? $arguments[3] : null, //控制显示结果数量 int $pagesize
				'page' => !empty($arguments[4]) ? $arguments[4] : null,  //控制显示结果页数 int $page
			);
			
			list($res, $count) = $this->parse_and_query($param);
			$result['count'] = $count;
			$result['item'] = $res;
			
			return $result;
		}
		
		// * <table>_list($show_filed=null, $orderby=null, $pagesize=null, $page=null)
		if( preg_match_all("/^(.*)_list$/i", $name, $matches)  )
		{
			$result = array();
			$collection_name = $matches[1][0];
			$this->collection = $this->mongodb->$collection_name;
			
			$param = array(
				'query_field_and_value' => null,  //查询字段 $query_field_and_value array($field1 => $val1, $field2 => $val2...)
				'show_field' => !empty($arguments[0]) ? $arguments[0] : null,  //显示字段 $show_field array($field1,field2...)
				'order_by' => !empty($arguments[1]) ? $arguments[1] : null, //排序方式 $orderby array($field => $type) $type 中 1为从小到大 -1为从大到小
				'pagesize' => !empty($arguments[2]) ? $arguments[2] : null, //控制显示结果数量 int $pagesize
				'page' => !empty($arguments[3]) ? $arguments[3] : null,  //控制显示结果页数 int $page
			);
			
			list($res, $count) = $this->parse_and_query($param);
			$result['count'] = $count;
			$result['item'] = $res;
			
			return $result;
		}
		
		// * <table>_update($item, $where_query=null) $where_query 字段必须为array($field => $val)
		if( preg_match_all("/^(.*)_update$/i", $name, $matches)  )
		{
			$where_query = array();
			
			$collection_name = $matches[1][0];
			$this->collection = $this->mongodb->$collection_name;
			
			$item = $arguments[0];
			if( empty($arguments[1]) )
			{
				//use 'id' as wheresql
				if( !isset($item['_id']) && !isset($item['_id_noo']) ) {
					echo "Db Handle Error When Update Collection: key '_id' is NOT defined!";return false;
				}else{
					if(isset($item['_id'])) {
						$where_query['_id'] = new MongoID($item['_id']);
					} else {
						$where_query['_id'] = $item['_id_noo'];
					}
					unset($item['_id'], $item['_id_noo']);
				}
			}else{
				//user define where_query
				$where_query = $arguments[1];
				if(!empty($where_query['_id'])){
					$where_query['_id'] = new MongoId($where_query['_id']);
				}
			}
			return $this->collection->update($where_query, array('$set' => $item));
		}
		
		// * <table>_delete_by_<query_field>($query_value, $where_query=null) $where_query 字段必须为array($field => $val)
		if( preg_match_all("/^(.*)_delete_by_(.*)$/i", $name, $matches)  )
		{
			$collection_name = $matches[1][0];
			$field_name = $matches[2][0];
			
			$this->collection = $this->mongodb->$collection_name;
			if($field_name == '_id'){
				$arguments[0] = new MongoId($arguments[0]);
			}
			$where_query = isset($arguments[1]) ? $arguments[1] : null;
			if( !empty($where_query) ) {
				if(isset($where_query['_id'])){
					$where_query['_id'] = new MongoID($where_query['_id']);
				}
				return $this->collection->remove(array_merge(array($field_name => $arguments[0]), $where_query), true);
			}else{
				return $this->collection->remove(array($field_name => $arguments[0]), true);
			}
		}
		
		// * <table>_add($item) 
		if( preg_match_all("/^(.*)_add$/i", $name, $matches)  )
		{
			$collection_name = $matches[1][0];
			$this->collection = $this->mongodb->$collection_name;
			
			$item = $arguments[0];
			
			$res = $this->collection->insert($item);
			
			$insert = $this->collection->findOne($item);
			
			return $res ? (string)$insert['_id'] : false;
		}
		
		// * <table>_distinct_by_<query_field>()
		if( preg_match_all("/^(.*)_distinct_by_(.*)$/i", $name, $matches) )
		{
			$collection_name = $matches[1][0];
			$query_field = $matches[2][0];
			$res = $this->mongodb->command(array("distinct" => $collection_name, "key" => $query_field));
			return 	$res['values'];
		}
		/* /__CALL */
		
		/* not found method */
		echo "[mongo_handle] Can't Find Method <b>{$name}</b>.\n";return false;
	}
	/**
	 * 解析并发起请求
	 * @param array() $options
	 * @return query_object
	 */
	private function parse_and_query($options = array())
	{
		global $_SGLOBAL;
		//debug info
		$debug_mongo_query = $options;
		
		$defalut_options = array(
			'query_field_and_value' => null,  //查询字段 $query_field_and_value array($field1 => $val1, $field2 => $val2...)
			'show_field' => null,  //显示字段 $show_field array($field1,field2...)
			'order_by' => null, //排序方式 $options['pagesize] array($field => $type) $type 中 1为从小到大 -1为从大到小
			'pagesize' => null, //控制显示结果数量 int $pagesize
			'page' => null,  //控制显示结果页数 int $page
		);
		$options = array_merge($defalut_options, $options);
		//判断是否有查询词
		if(!empty($options['query_field_and_value'])){
			//将id转化为id对象
			if(isset($options['query_field_and_value']['_id'])){
				$options['query_field_and_value']['_id'] = new MongoId($options['query_field_and_value']['_id']);
			}
			//判断是否有结果集范围
			if(!empty($options['show_field'])){
				$show_filed_query = array();
				foreach($options['show_field'] AS $val){
					$show_filed_query[$val] = 1; 
				}
				$res = $this->collection->find($options['query_field_and_value'], $show_filed_query);
			}else{
				$res = $this->collection->find($options['query_field_and_value']);
			}
		}else{
			$res = $this->collection->find();
		}
		$count = $res->count();
		//判断结果集返回顺序
		if(!empty($options['order_by'])){
			$res = $res->sort($options['order_by']);
		}
		//判断结果集数量
		if(!empty($options['pagesize'])){
			if(!empty($options['page'])){
				$res = $res->skip(($options['page'] - 1) * $options['pagesize'])->limit($options['pagesize']);
			}else{
				$res = $res->limit($options['pagesize']);
			}
		}
		$res = $this->object_to_array($res);
		$_SGLOBAL['debug_info']['mongo'][] = array('mongo_query' => $debug_mongo_query, 'time_cost' => (microtime(true) - $this->start_time));
		return array($res, $count);
	}
	/**
	 * 将mongodb查询结果对象转换为数组
	 * @param object $mongo_res_object
	 * @return array()
	 */
	private function object_to_array($mongo_res_object)
	{
		$final_res_array = array();
		$res_array = iterator_to_array($mongo_res_object);
		foreach($res_array AS $value){
			$final_res_array[] = $this->objectid_to_string($value);
		}
		return $final_res_array;
	}
	/**
	 * 将objectid对象转为string
	 * @param object $object_item
	 * @return array()
	 */
	private function objectid_to_string($object_item)
	{
		$res_arr = array();
		foreach($object_item AS $key => $value){
			if($key == '_id'){
				$res_arr[$key] = (string)$value;
			}else{
				$res_arr[$key] = $value;
			}
		}
		return $res_arr;
	}
	/**
	 * 从tripfm中搜索相关关键词，并唯一化
	 * @param string $keyword1
	 * @return array() relate_words
	 */
	public function get_relative_keyword($keyword1)
	{	
		$result = $sig_tmp = array();
		$res = $this->con->tripfm->RelaKeyword->find(array('keyword1' => array('$regex' => $keyword1)))->sort(array('relate' => -1));
		$res = $this->object_to_array($res);
		foreach($res AS $value){
			if(!isset($sig_tmp[$value['keyword2']])){
				$result[$value['category2']][] = $value;
				$sig_tmp[$value['keyword2']] = true;
			}
		}
		unset($sig_tmp);
		return $result;
	}
}
