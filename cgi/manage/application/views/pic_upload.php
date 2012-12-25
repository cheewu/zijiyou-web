<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
	<script type="text/javascript" src="/js/jquery.min.js"></script>
	<link href="/style/reset.css" rel="stylesheet" type="text/css" />
	<style>
	html { overflow-y: hidden; }
	body {width:100%;}
	.upload {width:800px;margin:0 auto;padding-bottom:20px;}
	#img_box {margin:0 auto;display:block;}
	</style>
</head>
<body>
<?php 
$img_url_origin = rawurlencode($this->query['img_url']);
$action = <<<HTML
/post/pic_upload?_id={$this->query['_id']}&collection={$this->query['collection']}&img_url=$img_url_origin
HTML;
$src = !empty($img_url) ? $img_url : rawurldecode($img_url_origin);
?>
	<form id="pic_upload" action="<?=$action?>" method="post" enctype="multipart/form-data">
	<input name="collection" type="hidden" value="<?=$this->query['collection']?>"/>
	<input name="_id" type="hidden" value="<?=$this->query['_id']?>"/>
	<div class="upload">
		<input id="pic_container" name="pic_container" type="file"/>
		<input id="pic_upload_button" type="button" value="上传" onclick="javascript:$('#pic_upload').submit()"/>
	</div>
	</form>
<?php 
	if(!empty($src)) {
	echo <<<HTML
	<img id="img_box" src="$src" />
HTML;
	}
?>
<script type="text/javascript">
$(window).load(function(){
	var width = parent.$("#pic_upload_iframe").width();
	if($("#img_box").width() > width) {
		var ratio = width / $("#img_box").width();
		var height = $("#img_box").height() * ratio;
		$("#img_box").css("width", width);
		$("#img_box").css("height", height);
	}
	parent.$("#pic_upload_iframe").css("height", document.body.offsetHeight);
});
<?php 
if( !empty($img_path) ) {
	echo <<<JS
parent.$('#img_path').val('$img_path');
JS;
}
?>
</script>
</body>
</html>