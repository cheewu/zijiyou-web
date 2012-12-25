<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>旅游景区首页</title>
<!--	css-->	
	<link href="/style/reset.css" rel="stylesheet" type="text/css" />
	<link href="/style/aug_css.css" rel="stylesheet" type="text/css" />
	<link href="/style/user_collection.css" rel="stylesheet" type="text/css" />
<!--	css-->	

<!--	jquery-->	
	<script type="text/javascript" src="/js/jquery.min.js"></script>
<!--	jquery-->	
</head>

<body id="wrapper_print">


<div id="wrapper">
	<div id="main_print">
<?php 
	foreach($collect_res AS $key => $value){
		if($value['category'] == 'POI' || $value['category'] == 'Region'){
			$config = $this->config->item(strtolower($value['category']));
			$config = $config['dbfield'];
		}else{
			$config = array('title' => '标题', 'content' => '正文');
		}
		foreach($config AS $k => $v){
				if(!empty($value[$k])){
				echo '<br/>';
				echo '<p><b>'.$v.': </b>'.strip_tags($value[$k]).'</p>'; 
				echo '<br/>';
				echo '<hr/>';
			}
		}
	}
?>
	</div>
</div>

<script type="text/javascript">
	//目录树展开
	$(".left_tree .hit_area").click(function(){
		$(this).toggleClass("expand");
		$(this).siblings("ul.tree_child").toggle();
	}); 
	//窗口高度
	$(window).resize(function(){
		var body_height = Math.max($(window).height(), $('#main_left').height(), $('#main_right').height()) - 35;
		$('body').css('height',body_height + 'px');
	});
	var body_height = Math.max($(window).height(), $('#main_left').height(), $('#main_right').height()) - 35;
	$('body').css('height',body_height + 'px');
</script>

</body>


</html>