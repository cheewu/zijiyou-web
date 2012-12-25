<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>article note</title>
<link href="/style/main.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div id="box">
	<div id="main" class="preview_main">
		<div class="preview">
			<div class="lv_title"><h1>标题：<a href="#"><?=$title?></a></h1><span><a href="#">收藏</a>　<a href="#">返回</a></span></div>
			<div class="lv_center">
				<h2><?=$title?></h2>
				<div class="article_poi">
					<?=nl2br($content)?>
				</div>
			</div>
		</div>
	</div>
</div>
</body>
</html>