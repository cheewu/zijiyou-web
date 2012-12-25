<?php



/**
 * var_dump var
 * 
 * @param mixed $var 需要打印的变量
 * @param bool $halt 是否在此中断
 *  
 */
function pr($var, $halt=1, $charset = 'utf-8')
{
	static $is_print_css=null;
	
	$backtrace = debug_backtrace();
	
	header("Content-type: text/html; charset={$charset}");
	
	if( $is_print_css === null ) {
		echo<<<EOF
<style>
body { color:#fff;background-color:#3c3c3c; }
a { color:#94aefb; }
.func { font-weight:bold;color:#1ad77c; }
.trace_header { background-color:#515252;padding:5px;font-size:12px; }
.var { color:#f9dd1d;margin:3 0 30 20px;border-left:2px solid #88a3f2;background-color:#515252;padding:5px;font-weight:500;font-size:14px; }
.trace { border-left:3px solid #39c4dd;padding-left:2px; }
</style>
EOF;
	        $is_print_css = true;
	}
	
	//函数堆栈
	echo "<div class='trace_header'>";
	$i=0;
	foreach ( $backtrace as $key=>$val ) {	   
	   echo "<div class='trace' style='margin-left:".($i*50)."px;'>";
	   $path_info = pathinfo($val['file']);
	   echo "<span class='func'>{$val['function']}()</span>, <a href='#' onclick='return false;' title=\"".h($val['file'])."\"><b>".($path_info['basename'])."</b></a>: <b>{$val['line']}</b>";
	   echo "</div>";
	   $i++;
	}	
	echo "</div>";
	
	//变量信息
	echo "<pre class='var'><code>";
    var_dump($var);
    echo '</pre></div>';
    
    //echo "</div>";
    
    if( $halt ) exit();
}

/**
 * htmlspecialchars
 * 
 * @param mixed $var
 * 
 * @return mixed
 */
function h($var)
{
    if( is_array($var) )
    {
        foreach ($var as $key=>$value)
        {
            $var[$key] = h($value);
        }
    } else {
        $var = htmlspecialchars($var);
    }
    return $var;
}

/**
 * 发起一个HTTP请求
 * 
 * @param string $url
 * @param array $options
 * 
 * @return string
 */
function shttp_request($url, $options = array())
{
	global $_SGLOBAL;
	//记录debuginfo
	$debug_time_start = microtime(true);
	//默认配置
	$default_options = array(
			'post_data' => array(), //可以关联数组，也可以直接是经过URL编码后字符串
			'headers' => array(), //http请求头信息, 格式为KEY=>VALUE形式
			'timeout' => 3, //sec, 超时，0为不限制
			'follow_loc' => 0, //是否跟踪Location跳转
			'output_header' => 0, //是否输出HTTP头信息
			'userpwd' => array(), //用户名和密码，需要验证时使用。格式：array('username', 'password')
			'maxredirs' => 5, // 最大跳转次数
			'halt' => 1, //遇到错误是否exit
		);
	$options = array_merge($default_options, $options);
	
    $ch = curl_init();
    
    //url
    curl_setopt($ch, CURLOPT_URL, $url);
	
    //instead of outputting it out directly
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //automatically set the Referer
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    //TRUE to follow any "Location: " header that the server sends
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $options['follow_loc'] ? true : false);    
    //maximum amount of HTTP redirections to follow
    curl_setopt($ch, CURLOPT_MAXREDIRS, $options['maxredirs']);
    //The number of seconds to wait whilst trying to connect. Use 0 to wait indefinitely
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $options['timeout']);
    
    if( !empty($options['headers']) ) {
    	$header_user_agent = 0;//is set user agent
    	foreach ($options['headers'] as $hkey=>$hval) {
    		if(strtolower(trim($hkey)) == 'user-agent') { $header_user_agent = 1; }
    		$nheaders[] = trim($hkey).": ".trim($hval);
    	}
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $nheaders);
    }
    
    //Set Default User-Agent
    if( empty($header_user_agent) ) {
    	//IE7 on Windows Xp
    	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)');
    }
	
    //TRUE to include the header in the output
    curl_setopt($ch, CURLOPT_HEADER, $options['output_header'] ? true : false);
    
    //HTTPS
    if( stripos($url, "https://") !== FALSE ) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
    
    //Set Username & Password
    if( !empty($options['userpwd']) ) {
    	curl_setopt($ch, CURLOPT_USERPWD, "[{$options['username']}]:[{$options['password']}]");
    }
    
    //post data
    if( !empty($options['post_data']) ) {
    	curl_setopt($ch, CURLOPT_POST, true);
    	if( is_array($options['post_data']) )
    	{
        	$encoded = "";
            foreach ( $options['post_data'] as $k=>$v)
            {   
                $encoded .= "&".rawurlencode($k)."=".rawurlencode($v);
            }
            $encoded = substr($encoded, 1);//去掉首个'&'
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
    	}else{
    	    curl_setopt($ch, CURLOPT_POSTFIELDS, $options['post_data']);
    	}
    }
    
    $res = curl_exec($ch);
    
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if( $res === FALSE || $httpStatus !== 200) {
        header("HTTP/1.0 500 Internal Server Error" , true , 500);
    	echo "[function shttp_request]REQUEST URL: {$url}，FAILURE! Error: ".curl_error($ch)."\n";
    	if($options['halt']) {
    		curl_close($ch);
    		exit();
    	}else{
    		return FALSE;
    	}
    }
    curl_close($ch);
    
    //debug_info
	$_SGLOBAL['debug_info']['solr'][] = array('request_url'=>$url, 'time_cost'=>(microtime(true)-$debug_time_start));
    
    return $res; 
}

/**
 * 
 * 解析simple_xml对象为关联数组
 * @param $xml xml_string
 * @param $array_tags 根据节点筛选
 * @return array() 关联数组
 * 
 */
function xml_2_arr($xml, $array_tags=array())
{
	$res = array();//结果集
	if(count($xml)==0){//没有子元素则输出该元素内容 
		return trim((string)$xml);//自动删除两头空字符 
	}
	foreach($xml AS $x_key => $x_val){
		$att = current($x_val->attributes());//获取该节点attributes
		if(!empty($att['name'])){//如果attributes name为空则用节点名称
			$name = $att['name'];
		}else {
			$name = $x_key;
		}
		if(count($att)>1){//如果attributes不止name一个属性则添加属性到该节点数组 
			foreach($att AS $att_key => $att_val){
				if($att_key == 'name'){
					continue;
				}else{
					$att_arr[$att_key] = $att_val;
				}
			}
		}
		if( !empty($array_tags) && in_array($name, $array_tags) ){ //$array_tags 过滤节点，筛选出所选节点 
			if(count($att)>1){//如果attributes不止name一个属性则添加属性到该节点数组 
				$res[$name][] = array_merge($att_arr,xml_2_arr($x_val,$array_tags));///递归 
			}else{
				$res[$name][] = xml_2_arr($x_val,$array_tags);//递归 
			}
		}else{
			if(count($att)>1){//如果attributes不止name一个属性则添加属性到该节点数组 
				$res[$name] = array_merge($att_arr,xml_2_arr($x_val,$array_tags));//递归 
			}else{
				$res[$name] = xml_2_arr($x_val,$array_tags);//递归 
			}
		}
	}
	return $res;
}

/**
 * 返回UTF-8编码第一个字符的A-Z
 * 
 * @param string $str
 * @return mixed
 */
function get_first_pinyin_char($str)
{
    static $py_hd = null;
	$temp_str=substr($str,0,1);
	$ascnum=Ord($temp_str);//得到字符串中第1字节的ascii码
	if ($ascnum>=224){  //如果ASCII位高与224，
		$first_str = substr($str,0,3); //根据UTF-8编码规范，将3个连续的字符计为单个字符,实际Byte计为3
	}else if($ascnum>=192){  //如果ASCII位高与192，
		$first_str = substr($str,0,2); //根据UTF-8编码规范，将2个连续的字符计为单个字符,实际Byte计为2
	}else if($ascnum>=65 && $ascnum<=90){  //如果是大写字母，实际的Byte数仍计1个
		return substr($str,0,1);
	}else{  //其他情况下，包括小写字母和半角标点符号，
		return strtoupper(substr($str,0,1));  //小写字母转换为大写,实际的Byte数计1个
	}
    
    if( empty($first_str) ) { return false; }
    //gbk拼音汉字对照文件
    if( !$py_hd ) {
        $py_hd = fopen(__DIR__.'/py.dat', 'r');
    }
    //将字符串的第一个字符转换为GBK编码获取其拼音
    $first_str = iconv("utf8", "gbk//IGNORE", $first_str);
    //匹配出第一个汉字或者字符
    preg_match("/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/", $first_str, $matches);
    $m_str = $matches[0];
    
    /* 未匹配上 */
    if( strlen($m_str) == 0 ) { return null; }
    
    /* 单个字符 */
    if( strlen($m_str) == 1 ) {
        $ascii = ord(strtoupper($m_str));
        if( $ascii >= 65 && $ascii <= 91 ) { return chr($ascii); }
        return null;
    }
    
    /* 汉字 */
    $high = ord($m_str[0]) - 0x81;
    $low = ord($m_str[1]) - 0x40;
    
    // 计算偏移位置
    $off = ($high<<8) + $low - ($high * 0x40);
    //读取数据
    fseek($py_hd, $off * 8, SEEK_SET);
    $ret = unpack('a8', fread($py_hd, 8));
    
    if( $ret ) { 
    	$first_char = strtoupper(substr($ret[1],0,1));
    	return $first_char; 
    }
    
    return null;
}
/**
 * 将note_id 转换为 article id
 * @param atring $note_id
 * @return string article id
 */
function note_id_to_article_id($note_id)
{
	$matches = array();
	preg_match_all("/([0-9a-z]+)\-\d+/", $note_id, $matches);
	return $matches[1][0];
}
/**
 * 根据ps获取页数
 * @param int $total_cnt
 * @param int $default_ps
 */
function get_total_page($total_cnt, $default_ps = 20)
{
	$total_pg_float = $total_cnt/$default_ps;
	$total_pg_int = intval($total_pg_float);
	return ($total_pg_float - $total_pg_int) > 0 ? $total_pg_int + 1 : $total_pg_int;
}
/**
 * utf-8 截断文字
 * @param 字符串 $string
 * @param 长度 $length
 * @param 编码 $coding 默认为utf-8编码
 */
function utf8_str_cut_off($string, $length, $coding = 'utf-8')
{
	if(!function_exists('mb_strlen')){
		echo 'Please install php extension mb_string';exit;
	}
	if(!mb_check_encoding($string, $coding)){
		echo 'Please input string encoded with utf-8';exit;
	}
	//去除img标签
	$string = preg_replace('/<\s*img[^>]*?>/i', '', $string);
	if(mb_strlen($string, $coding) > $length){
		return mb_substr($string, 0, $length - 3, $coding).'...';
	}else{
		return $string;
	}
}
/**
 * 过滤引号
 * @param string $string
 * @return string
 */
function google_map_strip_char($string)
{
	$filter_str = array("\"", "'", "\r\n", "\n", "\n\r", "\r");
	foreach($filter_str AS $value){
		$string = str_replace($value, '', $string);
	}
	return $string;
}
/**
 * 分页处理
 * 
 * @param int $num
 * @param int $perpage
 * @param int $curpage
 * @param string $mpurl
 * @param string $todiv
 * @param string $callback_func
 * @param array $callback_args array($param_1, $param_2)
 * 
 * @return string
 */
function multi($num, $perpage, $curpage, $mpurl, $todiv='', $callback_func=null, $callback_args=null) 
{
	$page = 5;
	
	$multipage = '';
	$mpurl .= strpos($mpurl, '?') ? (substr($mpurl,-1)!='?' ? '&':'') : '?';
	
	$realpages = 1;
	if($num > $perpage) {
		$offset = 2;
		$realpages = @ceil($num / $perpage);
		$pages =  $realpages;
		if($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $from + $page - 1;
			if($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if($to - $from < $page) {
					$to = $page;
				}
			} elseif($to > $pages) {
				$from = $pages - $page + 1;
				$to = $pages;
			}
		}
		$multipage = '';
		$urlplus = $todiv?"#$todiv":'';
		if($curpage > 1) {
			$multipage .= "<a ";
			$multipage .= "href=\"{$mpurl}pg=".($curpage-1)."$urlplus\"";
			//$multipage .= " class=\"prev\">&lsaquo;&lsaquo;</a>";
			$multipage .= " class=\"prev\">上一页</a>";
		}
		if($curpage - $offset > 1 && $pages > $page) {
			$multipage .= "<a ";
			$multipage .= "href=\"{$mpurl}pg=1{$urlplus}\"";
			$multipage .= " class=\"first\">1 ...</a>";
		}
		for($i = $from; $i <= $to; $i++) {
			if($i == $curpage) {
				$multipage .= '<strong>'.$i.'</strong>';
			} else {
				$multipage .= "<a ";
				$multipage .= "href=\"{$mpurl}pg=$i{$urlplus}\"";
				$multipage .= ">$i</a>";
			}
		}
		if($to < $pages) {
			$multipage .= "<a ";
			$multipage .= "href=\"{$mpurl}pg=$pages{$urlplus}\"";
			$multipage .= " class=\"last\">... $realpages</a>";
		}
		if($curpage < $pages) {
			$multipage .= "<a ";
			$multipage .= "href=\"{$mpurl}pg=".($curpage+1)."{$urlplus}\"";
			//$multipage .= " class=\"next\">&rsaquo;&rsaquo;</a>";
			$multipage .= " class=\"next\">下一页</a>";
		}
/* 		if($multipage) {
			$multipage = '<em>&nbsp;'.$num.'&nbsp;</em>'.$multipage;
		} */
		
		/* callback */
		if( !empty($callback_func) ) {
    		if( preg_match_all("/href=\"(.*?)\"/", $multipage, $matches) ) {
    		    if( !empty($matches[1]) ) {
                    foreach ($matches[1] as $val) {
                        /* callback function */
                        //callback args
                        $args = !empty($callback_args) ? array_merge(array($val), $callback_args) : array($val);
                        
                        //新的URL格式
                        $new_val = call_user_func_array($callback_func, $args);
                        
                        //replace to new url
                        $multipage = str_replace('"'.$val.'"', '"'.$new_val.'"', $multipage);
                    }
    		    }
            }
		}
        /* /callback */
        
	}
	return $multipage;
}


/**
 * 拼接请求参数
 * @param 源url $url_origin
 * @param 待拼接参数 $append_url
 * @param 链接符号 $implode_char
 */
function append_request_url($url_origin, $append_url, $implode_char = '/')
{
	if(substr($this->url, -1) == $implode_char || substr($append_url, 0, 1) == $implode_char){
		$url_origin .= $append_url;
	}else{
		$url_origin .= $implode_char.$append_url;
	}
	return $url_origin;
}

/**
 * 获取关键词长度和语种
 * @param string $keyword
 * @return array()
 */
function get_keyword_len_lan($keyword)
{
	$res = array();
	if($keyword == strtoupper($keyword)){
		$res['count'] = mb_strlen($keyword, 'utf-8');
		$res['type'] = 'cn';
	}elseif(strlen($keyword) == mb_strlen($keyword, 'utf-8')){
		$matches = array();
		$res['count'] = 0;
		$ex_res = explode(" ", $keyword);
		foreach($ex_res AS $val){
			$tmp_val = trim($val);
			if(!empty($tmp_val)){
				$res['count'] ++;
			}
		}
		$res['type'] = 'en';
	}else{
		$res['count'] = -1;
		$res['type'] = 'mix';
	}
	return $res;
}

/**
 * 将solr的关键词规范化为链接
 * @param string $note_keyword_str
 * @return string 
 */
function note_tag_link($note_keyword_str, $class = '')
{
	$res_string = '';
	$key_word_arr = explode(" ", $note_keyword_str);
	foreach($key_word_arr AS $value){
		$res_string .= '<a href="/search?q='.rawurlencode($value).'"';
		$res_string .= empty($class) ? '' : 'class="'.$class.'"';
		$res_string .= '>'.$value.'</a>';
	}
	return $res_string;
}

/**
 * 递归删除左右空字符
 * @param mix $to_be_trim
 * @return mix 删除后的结果
 */
function recursive_trim($to_be_trim)
{
	if(is_array($to_be_trim)){
		foreach($to_be_trim AS $key => $value){
			$res[$key] = recursive_trim($value);
		}
	}else{
		return trim($to_be_trim);
	}
	return $res;
}

/**
 * 递归rawencode
 * @param mix $to_be_trim
 * @return mix 编码后的结果
 */
function recursive_rawurlencode($to_be_trim)
{
	if(is_array($to_be_trim)){
		foreach($to_be_trim AS $key => $value){
			$res[$key] = recursive_rawurlencode($value);
		}
	}else{
		return rawurlencode($to_be_trim);
	}
	return $res;
}

/**
 * 递归rawdecode
 * @param mix $to_be_trim
 * @return mix 编码后的结果
 */
function recursive_rawurldecode($to_be_trim)
{
	if(is_array($to_be_trim)){
		foreach($to_be_trim AS $key => $value){
			$res[$key] = recursive_rawurldecode($value);
		}
	}else{
		return rawurldecode($to_be_trim);
	}
	return $res;
}

/**
 * ADDSLASHES
 * 
 * @param string $string
 * 
 * @return string $string
 */
function saddslashes($string) {
	$magic_quote = get_magic_quotes_gpc();
	if(empty($magic_quote)){
		$string = recursive_addslashes($string);
	}
	return $string;
}
/**
 * 递归调用addslashes
 * @param string $string
 * @return string $string
 */
function recursive_addslashes($string)
{
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[addslashes($key)] = saddslashes($val);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

/**
 * 获取客户端IP
 */
function get_onlineip() {
	$onlineip = '';
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$onlineip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$onlineip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$onlineip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	return $onlineip;
}


/**
 * 模板包含函数，根据配置文件选择模板文件夹
 * @param string $file_name
 */
function template($file_name, $param = array())
{
	$ci = &get_instance();
	$ci->config->load('config_run');
	$tmplate_folder = $ci->config->item('template_folder');
	if(!empty($tmplate_folder)){
		$file_name = $tmplate_folder.'/'.$file_name;
	}
	if(substr($file_name, -4) != '.php'){
		$file_name .= '.php';
	}
	$ci->load->view($file_name, $param);
}

/**
 * 从wikipedia集合中找content信息
 * @param string $name
 * @return array()
 */
function get_wiki_content($name, $id = array())
{
	$ci = &get_instance();
	$search = array('title' => $name);
	!empty($id) && $search['_id'] = array('$nin' => $id);
	//查询
	$wiki = $ci->mongo_db->Wikipedia_fetch($search);
	if(!empty($wiki) && !empty($wiki['content'])){
		//排除已经查过的
		$id[] = $wiki['_id'];
		//如果有跳转则递归
		if(preg_match("/#REDIRECT\s?(.+)/i", $wiki['content'], $match)){
			$wiki = get_wiki_content($match[1], $id);
		}
	}
	return $wiki;
}

/**
 * fan2jian
 * @param string $fan_str
 * @param string $encoding
 * @return string
 */
function fan2jian($fan_str, $encoding = 'utf-8')
{
  static $map;
  if (empty($map)) { 
    $data = file_get_contents(dirname(__FILE__) . '/gbbig.txt');
    $char_cnt = mb_strlen($data, 'utf-8');
    for ($i = 0; $i < $char_cnt; $i = $i + 2) {
      $trans = mb_substr($data, $i, $i + 2, 'utf-8');
      $map[mb_substr($trans, 1, 1, 'utf-8')] = mb_substr($trans, 0, 1, 'utf-8');
    }
  }
  $str = @iconv($encoding, "utf-8//ignore", $fan_str);
  if (!$str) return "";
  $utf8_cnt = mb_strlen($str, "utf-8");
  for ($jian_str = "", $i = 0; $i < $utf8_cnt; $i++) {
    $char_utf8 = mb_substr($str, $i, 1, "utf-8");
    if (strlen($char_utf8) < 2 || !isset($map[$char_utf8])) { 
      $jian_str .= $char_utf8; continue; 
    }
    $jian_str .= $map[$char_utf8];
  }
  return $jian_str;
}

/**
 * 从wikipedia集合中找center
 * @param string $name
 * @return array()
 */
function get_wiki_center($name, $id = array()) {
    $ci = &get_instance();
	$search = array('title' => $name);
	!empty($id) && $search['_id'] = array('$nin' => $id);
	//查询
	$wiki = $ci->mongo_db->Wikipedia_fetch($search, array('center', 'content'));
	if(!empty($wiki) && !empty($wiki['center'])){
		//排除已经查过的
		$id[] = $wiki['_id'];
		//如果有跳转则递归
		if(preg_match("/#REDIRECT\s?(.+)/i", $wiki['content'], $match)){
			$wiki = get_wiki_center($match[1], $id);
		}
	}
	return isset($wiki['center']) ? array($wiki['center'][1], $wiki['center'][0]) : false;
}

/**
 * get mongo increment
 * @param string $dbname
 * @param string $collection
 * @param string field
 * @return int
 */
function get_mongo_increment($dbname, $collection, $field) {
  $ci = &get_instance();
  $con = $ci->mongo_db->con;
  $cmd = array(
    'findAndModify' => 'autoIncrement',
    'query' => array('field' => $field, 'collection' => $collection),
    'update' => array('$inc' => array('index' => 1)),
    'new' => true,
  );
  $res = $con->$dbname->command($cmd);
  return $res['value']['index'];
}

/**
 * 繁体转简体
 * @param string $string
 * @return string $string
 */
function fan_to_jian($string){
	$big2gb = new big2gb;
	return $big2gb->chg_utfcode($string, "utf-8");
}

class big2gb
{
	function chg_utfcode($str,$charset='big5')
	{
		if ($charset=='big5')
		{
			$fd = fopen(__DIR__ . "/gb2big.map",'r');
			$str1 = fread($fd,filesize(__DIR__ . "/gb2big.map"));
		}
		else 
		{
			$fd = fopen(__DIR__ . "/big2gb.map",'r');
			$str1 = fread($fd,filesize(__DIR__ . "/big2gb.map"));
		}
		fclose($fd);
		
		// convert to unicode and map code
		$chg_utf = array();
		for ($i=0;$i<strlen($str1);$i=$i+4)
		{
			$ch1=ord(substr($str1,$i,1))*256;
			$ch2=ord(substr($str1,$i+1,1));
			$ch1=$ch1+$ch2;
			$ch3=ord(substr($str1,$i+2,1))*256;
			$ch4=ord(substr($str1,$i+3,1));
			$ch3=$ch3+$ch4;
			$chg_utf[$ch1]=$ch3;
		}
		
		// convert to UTF-8
		$outstr='';
		for ($k=0;$k<strlen($str);$k++)
		{
			$ch=ord(substr($str,$k,1));
			if ($ch<0x80)
			{
				$outstr.=substr($str,$k,1);
			}
			else
			{
				if ($ch>0xBF && $ch<0xFE)
				{
					if ($ch<0xE0) {
						$i=1;
						$uni_code=$ch-0xC0;
					} elseif ($ch<0xF0)	{
						$i=2;
						$uni_code=$ch-0xE0;
					} elseif ($ch<0xF8)	{
						$i=3;
						$uni_code=$ch-0xF0;
					} elseif ($ch<0xFC)	{
						$i=4;
						$uni_code=$ch-0xF8;
					} else {
						$i=5;
						$uni_code=$ch-0xFC;
					}
				}
	
				$ch1=substr($str,$k,1);
				for ($j=0;$j<$i;$j++)
				{
					$ch1 .= substr($str,$k+$j+1,1);
					$ch=ord(substr($str,$k+$j+1,1))-0x80;
					$uni_code=$uni_code*64+$ch;
				}
				
				if (!isset($chg_utf[$uni_code]))
				{
					$outstr.=$ch1;
				}
				else
				{
					$outstr.=$this->uni2utf($chg_utf[$uni_code]);
				}
				$k += $i;
			}
		}
		return $outstr;
	}

	// Return utf-8 character
	function uni2utf($uni_code)
	{
		if ($uni_code<0x80) return chr($uni_code);
		$i=0;
		$outstr='';
		while ($uni_code>63) // 2^6=64
		{
			$outstr=chr($uni_code%64+0x80).$outstr;
			$uni_code=floor($uni_code/64);
			$i++;
		}
		switch($i)
		{
			case 1:
				$outstr=chr($uni_code+0xC0).$outstr;break;
			case 2:
				$outstr=chr($uni_code+0xE0).$outstr;break;
			case 3:
				$outstr=chr($uni_code+0xF0).$outstr;break;
			case 4:
				$outstr=chr($uni_code+0xF8).$outstr;break;
			case 5:
				$outstr=chr($uni_code+0xFC).$outstr;break;
			default:
				echo "unicode error!!";exit;
		}
		return $outstr;
	}
}

/**
 * 获取google图钉icon
 * @param int $count（1->A）最多到26即Z
 */
function google_map_icon_url($count){
	return 'http://www.google.com/mapfiles/marker'.chr($count + 65).'.png';
}
/**
 * 经纬度距离转换为国标距离
 * @param unknown_type $lt_lg_dis
 */
function lt_lg_dis_to_real_dis($lt_lg_dis, $type = 'km', $is_format = true){
	$earth_radius = 6378.137;//km
	$pi = 3.1415926;//元周率
	$ratio = ( (2 * $pi) / 360 ) * $earth_radius;
	$real_dis =  $lt_lg_dis * $ratio;
	if(!$is_format){
		$output =  $type == 'm' ? $real_dis * 1000 : $real_dis;
	}else{
		$output = dis_format($real_dis);
	}
	return $output;
}

/**
 * 国标距离换为经纬度距离转
 * @param unknown_type $lt_lg_dis
 */
function real_dis_to_lt_lg_dis($real_dis){
	$earth_radius = 6378.137;//km
	$pi = 3.1415926;//元周率
	$ratio = ( (2 * $pi) / 360 ) * $earth_radius;
	return $real_dis / $ratio;
}


/**
 * 格式化距离
 * @param string $dis
 * @return string $output
 */
function dis_format($dis){
	if($dis > 1){
		$output =  intval($dis * 10) / 10;
		$output .= 'km';
	}else{
		$output = intval($dis * 10) * 100;
		$output .= 'm';
	}
	return $output;
}


/**
 * 拼接url参数
 * @param array() $url_sets
 * @return string 
 */
function implode_url_set($url, $url_sets){
	foreach($url_sets AS $key => $value){
		$url = url_append($url, $key, $value);
	}
	return $url;
}

/**
 * url添加参数
 * @param string $url url
 * @param string $key 参数名
 * @param string $value 参数值value
 * @param bool $allow_empty 是否允许value为空
 * @return string 拼接后的url
 */
function url_append($url, $key, $value = '', $allow_empty = false){
	if(empty($value) && !$allow_empty){
		return $url;
	}
	$url .= strpos($url, '?') ? (substr($url,-1)!='?' ? '&':'') : '?';
	$url .= $key.'='.$value;
	return $url;
}

/**
 * 移除某一个q2的字段
 * @param string $url
 * @param string $word
 * @return string
 */
function remove_one_q2_word($url, $word){
	$regex = array("/(\+{$word})/", "/({$word}\+)/", "/(q2={$word}&)/", "/([\?&]q2={$word})$/");
	foreach($regex AS $value){
		if(preg_match($value, $url)){
			return preg_replace($value, '', $url);
		}
	}
	return $url;
}

/**
 * 给地铁线路加上wiki link
 * @param array() $lines
 * @return array() 
 */
function tpl_add_line_wiki_link($lines)
{
	$tmp_lines_link = array();
	foreach($lines AS $value){
		$tmp_lines_link[] = '<a class="info_preview" href="/search/wiki_preview/'.h($value).'">'.$value.'</a>';
	}
	return $tmp_lines_link;
}


/**
 * json unicode to utf-8
 */
function json_unicode_to_utf8($json){
	$json = preg_replace_callback("/\\\u([0-9a-f]{4})/", create_function('$match', '
		$val = intval($match[1], 16);
		$c = "";
		if($val < 0x7F){        // 0000-007F
			$c .= chr($val);
		} elseif ($val < 0x800) { // 0080-0800
			$c .= chr(0xC0 | ($val / 64));
			$c .= chr(0x80 | ($val % 64));
		} else {                // 0800-FFFF
			$c .= chr(0xE0 | (($val / 64) / 64));
			$c .= chr(0x80 | (($val / 64) % 64));
			$c .= chr(0x80 | ($val % 64));
		}
		return $c;
	'), $json);
	return $json;
}

/**
 * array to xml
 * @param array $array
 * @param string $root_node_name
 * @return string
 */
function array_to_xml($array, $root_node_name = "") {
	$body = _array_to_xml($array, 'root');
	return '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>'."<$root_node_name>$body</$root_node_name>";
}

/**
 * recrusive array to xml
 * @param array $array
 * @param father node name $father_node_name
 * @param child level $level
 * @return string
 */
function _array_to_xml($array, $father_node_name, $level = 0) {
	$res = "";
	if(!is_array($array)) { return "$array"; }
	foreach($array AS $node_name => $node) {
		$is_multi_child = _is_multi_child($node) || (is_array($node) && count($node) == 1 && isset($node[0]));
		is_int($node_name) && $node_name = $father_node_name;
		$space = str_repeat("    ", $level);
		!$is_multi_child && $res .= $space."<$node_name>";
		is_array($node) && !$is_multi_child && $res.= "\n";
		$res .= _array_to_xml($node, $node_name, $level);
		is_array($node) && $res.= $space;
		!$is_multi_child && $res .= "</$node_name>\n";
	}
	$level ++;
	return $res;
}

/**
 * is has mutil child
 * @param array $array
 * @return bool
 */
function _is_multi_child($array) {
	$child_count = count($array);
	if($child_count <= 1) { return false;}
	for ($i = 0; $i < $child_count; $i ++ ) {
		if(!isset($array[$i])) { return false; }
	}
	return true;
}

/**
 * wikicategory xml parse
 * @param xml body $wiki_post
 * @return array
 */
function wikicategory_parse($wiki_post) {
	$ci = &get_instance();
	$conf = $ci->config->item('region_field');
	$multi_node_name = $conf['wikicategory'];
	$simple_xml_obj = simplexml_load_string('<?xml version="1.0" encoding="utf-8" standalone="yes" ?><root>'.$wiki_post.'</root>');
	return xml_2_arr($simple_xml_obj, $multi_node_name);
}




