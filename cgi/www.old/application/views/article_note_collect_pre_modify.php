<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
	<script type="text/javascript" src="/js/jquery.min.js"></script>
	<link href="/style/css.css" rel="stylesheet" type="text/css" />
</head>

<body id="arti_note">
	<div id="travel_background">
		<form name="editForm" id="editForm" action="/user/creat_collection" method="post">
			<input type="hidden" name="collect_type" value="<?=$type?>"/>
			<input type="hidden" name="_id" value="<?=$result['_id']?>"/>
			<div id="box">
				<div class="lv_center">
					<h1>详情</h1>
					<ol>
						<dt>标题</dt>
						<dd><input name="title" type="text" class="lv_field" value="<?=$result['title']?>"/></dd>
					</ol>
					<ol>
						<dt class="lv_field_area_box">文章内容</dt>
						<dd class="arti_box"><textarea name="content" class="lv_field_area_arti"><?=$result['content']?></textarea></dd>
					</ol>
					<ol>
						<dd>
							<div class="lv_button"><label> <input type="button" class="button" value="收藏" onclick="javascript:toSave();"/> </label>
								<label><input type="button" class="button" value="返回" onclick="javascript:parent.$.fancybox.close();"/> </label>		
							</div>
						</dd>
					</ol>
					<div style="clear:both;"></div>
				</div>
				<div style="clear:both;"></div>
			</div>
		</form>
		<div class="travel_bottomnavbg"></div>
	</div>
</body>
<script type="text/javascript">
function toSave() {
	var cate = $('input[name="collect_type"]').val();
	var id = $('input[name="_id"]').val();
	$.ajax({
		'type': "GET",
		'url': "/user/ajax_collection_check/",
		'dataType': 'text',
		'data': 'cate=' + cate + '&id=' + id,
		'success': function(sig){
			if(sig == 'notlogin'){
				window.location = '/user/login';
			}else if(sig == 'collected'){
				alert('您已经收藏过此项目，请勿再次收藏');
			}else{
				$('#editForm')[0].submit();
			}
		}
	});
}
</script>
</html>
