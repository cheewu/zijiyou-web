<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
	<title><?=$title?></title>
	<link href="/style/keywordcss.css" rel="stylesheet" type="text/css" />
	<link href="/style/gaopeng.css" rel="stylesheet" type="text/css" />
	<link href="/style/search.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="/js/jquery.min.js"></script>
	<!--fancybox-->
	<script src="/fancybox/jquery.mousewheel-3.0.4.pack.js" type="text/javascript"></script>
	<script type="text/javascript" src="/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
	<link rel="stylesheet" type="text/css" href="/fancybox/jquery.fancybox-1.3.4.css" media="screen"/>
	<!--fancybox-->
</head>
<body>
<style>
body { 
	background-color:transparent;
	width:300px;
	height:200px; 
	overflow:hidden;
}
.fancy_head {
	height:40px;
	width:100%;
	background:url("/images/pop_title_center.gif") repeat-x;
	font-size:15px;
	font-weight:bold;
	line-height:40px;
	padding-left:20px;
}
.text{
	font-size:13px;
	margin-top:5px;
}
.fancy_area{
	padding-top:10px;
	padding-left:5px;
	padding-right:10px;
}
.area_select{
	margin-top:5px;
}
.fright{
	float:right;
}
.fleft{
	float:left;
}
.area_submit{
	padding:5px;
}
.area_delete{
	padding:5px;
}
</style>

<div class="fancy_head">
	<?=$title?>
</div>
<div class="fancy_area">
	<div>
		<ul>
			<li>
				<input class="text fleft" name="modi_keyword" id="modi_keyword" type="text" value="<?=$result['keyword'] ?>"></input>
				<input id="submit_form" class="area_submit fright" type="submit" value="提交"></input>
				<div class="clear"></div>
			</li>
			<li>
				<select name="modi_category" id="modi_category" class="area_select">
				<?php 
					foreach($category AS $val){
						if($val == $result['category']){
							$selected = 'selected="selected"';
						}else{
							$selected = '';
						}
						echo "<option value=\"{$val}\" {$selected}>{$val}</option>";
					}
				?>
				<input id="delete_form" class="area_delete fright" type="submit" value="删除"></input>
				<input name="_id" id="modi_id" value="<?=$result['_id'] ?>" style="display:none;"></input>
				<div class="clear"></div>
				</select>
			</li>
		</ul>
	</div>
</div>
<script language="javascript">
$(document).ready(function() {
	$('#submit_form').click(function(){
		var category = $('#modi_category').val();
		var keyword = $('#modi_keyword').val();
		var id = $('#modi_id').val();
		$.ajax({
			'type': "GET",
			'url': "/keyword/modified_ajax/",
			'dataType': 'html',
			'data': "category=" + category + "&keyword=" + keyword + '&id=' + id,
			'success': function(sig){
				if(sig == 'nochange'){
					alert('并没有做任何修改哇');
				}
				if(sig == 'success'){
//					parent.$.fancybox.close();
					parent.location.reload();
				}
			}
		});
	});
	$('#delete_form').click(function(){
		var category = $('#modi_category').val();
		var keyword = $('#modi_keyword').val();
		var id = $('#modi_id').val();
		$.ajax({
			'type': "GET",
			'url': "/keyword/remove_ajax/",
			'dataType': 'html',
			'data': 'id=' + id,
			'success': function(sig){
				if(sig == 'success'){
					parent.location.reload();
				}
			}
		});
	});
})
</script>

</body>
</html>





<?php
