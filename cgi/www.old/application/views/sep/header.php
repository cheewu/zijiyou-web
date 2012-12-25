<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php 
//全局变量
global $_SGLOBAL;
//pr($_SGLOBAL);
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php 
$ci = &get_instance();
$tpl_path = $ci->config->item('template_folder');
//全局变量
?>
<script type="text/javascript">var tpl_path = '<?=$tpl_path?>'</script>
	<title>旅游景区首页</title>
<!--	css-->	
	<link href="/style/reset.css" rel="stylesheet" type="text/css" />
	<link href="/style/main.css" rel="stylesheet" type="text/css" />
<!--	css-->	

<!--zijiyou js-->
	<script type="text/javascript" src="/js/zjy_map.js"></script>
<!--zijiyou js-->

<!--	jquery-->	
	<script type="text/javascript" src="/js/jquery.min.js"></script>
<!--	jquery-->	
<?php if(empty($is_index) || $is_index !== true){//首页不加载google js，提高首页访问速度 ?>
<!--	google api-->	
	<script type="text/javascript" src="http://ditu.google.cn/maps/api/js?sensor=false"></script>
<?php /* ?>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript" src="http://www.panoramio.com/wapi/wapi.js?v=1&hl=ch"></script>
	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
	<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false&libraries=panoramio"></script>
 <?php */ ?>
<!--	google api-->
<?php }?>
<!--	fancybox-->
	<link rel="stylesheet" type="text/css" href="/fancybox/jquery.fancybox-1.3.4.css" media="screen"/>
	<script type="text/javascript" src="/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
	<script src="/fancybox/jquery.mousewheel-3.0.4.pack.js" type="text/javascript"></script>
<!--	fancybox-->

<!--autocomplete-->
	<link href="/style/autocomplete.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="/js/autocomplete.js"></script>
<!--autocomplete-->
</head>

<body>
<div id="header">
	<div class="poinbust">
		<div class="imgbslet"><img src="/images/sep/header_img_06.png" /></div>
		<div class="imgbsbg">
			<ul>
				<li><a href="#"><font>3</font>个旅游目的地</a></li>
				<li><a href="#"><font>10</font>个景点</a></li>
				<li><a href="#"><font>15</font>个贴士</a></li>
			</ul>
			<span><a href="#"><img src="/images/sep/header_img_07.jpg" /></a></span>
		</div>
		<div class="imgbslet"><img src="/images/sep/header_img_09.png" /></div>
	</div>
	<div class="usitont">
		<div class="topuser">
<?php 
/*		if(!empty($user_detail)){
				foreach(array('user_img', 'user_name', 'collect_button', 'user_collection_count', 'collection_print') AS $value){
					echo '<div class="tool_manage '.($value == 'user_collection_count' ? 'collection_count' : '').' fleft">';
					//头像
					if($value == 'user_img'){
						echo '<img src="'.$user_detail['user_img'].'"></img>';
					}
					//名称
					if($value == 'user_name'){
						echo '<span>'.$user_detail['user_name'].'</span>';
					}
					//收藏
					if($value == 'collect_button'){
						echo '<a href="/user/collection" target="_blank"><span>收藏夹</span></a>';
					}
					//收藏数
					if($value == 'user_collection_count'){
						echo '<a href="/user/collection" target="_blank"><span>'.$user_detail['user_collection_count'].'</span></a>';
					}
					//打印收藏
					if($value == 'collection_print'){
						echo '<a href="/user/collection?print=true" target="_blank"><span>收藏打印</span></a>';
					}
					echo '</div>';
				}
			}else{
				echo '
					<div class="tool_manage fleft">
						<a href="/user/login"><span>请登录</span></a>
					</div>';
			}
			*/
//获取用户信息
$user_detail = $ci->users_handle->user_detail;
if(!empty($user_detail)){
	echo '
			<a href="#">
				<img src="'.$user_detail['user_img'].'" border="0" />
			</a>
			<span>
				<a href="/user/collection">我的收藏夹</a>
				<a href="/user/collection?print=true">打印攻略</a>
			</span>';
}else{
	echo '
			<span>
				<a href="/user/login">登陆</a>
				<a href="/user/login">注册</a>
			</span>';
}
?>
		</div>
	</div>
<?php if(empty($is_index)){?>
	<div class="headcont">
		<div class="search">
			<div class="logo"><img src="/images/sep/logo.jpg" width="177" height="48"></img></div>
			<div class="searchs">
				<form id="search_box" action="/search" method="get" >
					<input id="query" name="q" type="text" class="searfont" />
					<a id="query_button" class="searimg" onclick="javascript:$('#search_box').submit();"></a>
					<div class="clear"></div>
				</form>
			</div>
		</div>

<?php /* if($hidden_header_nav !== TRUE) { ?>
		<div class="nav">
			<ul>
				<li><a href="#">首页</a></li>
				<li><a href="#">目的地指南</a></li>
				<li><a href="#">旅游攻略</a></li>
			</ul>
		</div>
<?php } */?>
		<div class="clear"></div>
	</div>
<?php }?>
</div>
<!--主要内容-->
<div id="main">
    