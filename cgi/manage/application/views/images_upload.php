<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
<title>上传图片</title>
<script type="text/javascript" src="/js/jquery-1.8.0.min.js"></script>
<link href="/style/reset.css" rel="stylesheet" type="text/css" />
<style type="text/css">
html { width:500px; }
#header { background-color: #4f4f4f; height:30px; }
#upload { width:300px; margin:10px auto 5px auto; text-align:center;}
#upload-file { width:150px; }
</style>
<script type="text/javascript">
<?php if ($code == 200) {?>
alert("上传失败，请重新上传");
<?php }?>
</script>
</head>
<body>
<div id="wrapper">
  <div id="header"></div>
  <form id="upload" action="/images/upload/<?=$collection?>/<?=$mongoId?>" method="post" enctype="multipart/form-data">
  	<input id="upload-file" type="file" name="upload-img"/>
  	<input id="upload-botton" type="button" value="上传" onclick="$('#upload').submit();"/>
  </form>
</div>
<?php if ($code == 100) {?>
<div id="tpl" class="img-box" imageId="<?=$picid?>" mongoId="<?=$mongoId?>">
  <div class="img-handle" style="display:none;">
    <a class="img-handle-edit"><i class="icon-pencil"></i></a>
    <a class="img-handle-delete"><i class="icon-remove-sign"></i></a>
  </div>
  <img src="<?=$url?>" />
</div>
<script type="text/javascript">
var item = document.getElementById('tpl');
item.id = '';
var url = $(item).children('img').attr('src');
var aTag = document.createElement('a');
$(item).children('img').attr('src', url + '150x150?123');
aTag.href = url;
aTag.target = '_blank';
aTag.appendChild(item);
parent.document.getElementById('<?=$mongoId?>').appendChild(aTag);
parent.$.fancybox.close();
parent.imageHandle();
</script>
<?php }?>

</body>
</html>

