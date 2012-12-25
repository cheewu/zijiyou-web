<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>旅游景区首页</title>
	<link href="/style/aug_css.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="/js/jquery.min.js"></script>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<!--	fancybox-->
	<link rel="stylesheet" type="text/css" href="/fancybox/jquery.fancybox-1.3.4.css" media="screen"/>
	<script type="text/javascript" src="/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
	<script src="/fancybox/jquery.mousewheel-3.0.4.pack.js" type="text/javascript"></script>
	<script type="text/javascript" src="http://www.panoramio.com/wapi/wapi.js?v=1&hl=ch"></script>
	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
<!--	fancybox-->
<!--autocomplete-->
	<link href="style/autocomplete.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="/js/autocomplete.js"></script>
<!--autocomplete-->
</head>

<body>
    <div id="box">
        <div id="header">
            <div class="logo"><img src="/images/tour_logo.gif" width="300" height="60" /></div>
<!--            <div class="registration"><a href="E">登陆</a> <a href="E">注册</a></div>-->
        </div>
        <div id="nav">
            <div class="menu">
                <ul>
                    <li><a href="/search">首页</a></li>
<!--                    <li><a href="#">目的地指南</a></li>-->
<!--                    <li><a href="#">旅游攻略</a></li>-->
                </ul>
            </div>
            <div class="search">
	            <form name="search" id="search" maxlength="100" autocomplete="off" action="/search" method="get">
		            <span><input name="q" id="textbox" type="text" value="<?=$get_sets['q'] ?>"></input></span>
		            <label for="nav_submit"><img class="nav_submit_img" src="/images/tour_search.gif"></img></label><input type="submit" id="nav_submit" style="display:none;"></input>
	            </form>
            </div>
        </div>
        <div class="scenery">
            <div class="scenery_page">
            <h1><?=$title ?></h1>
<!--            <h2>过去(1018)</h2>-->
<!--            <h3>想去(1043)</h3>-->
            </div>
        </div>
        
		
        <div id="main">
            <div id="left">
                <div class="left_whole">
                    <div class="left_content">
						<a href="/search/?q=<?=$title ?>">返回</a>
                    </div>
                </div>
            </div>
            <div id="right">
<?php 
	echo '<div class="right_travel">';
		echo "<h1>{$title} 文章</h1>";

		foreach($article AS $value){
			echo '<div class="travelnotes travel_article">';
				echo '<a href="#'.$value['_id'].'" class="article_fancybox"><div class="right_title contents" >';
					echo utf8_str_cut_off($value['content'], 200);
				echo '</div></a>';
				echo "<div style=\"display: none;\"><div id=\"{$value['_id']}\" style=\"width:800px;height:500px;overflow:auto;\">".nl2br($value['content'])."</div></div>";
				echo "<h3><em>标题：{$value['title']}</em>";
				if(!empty($value['author'])){
					echo "<em>作者：{$value['author']}</em>";
				}
					echo "<em>时间：".date("Y-m-d",strtotime($value['publishDate']))."</em></h3>";
				echo "<h4>原文：<a href=\"{$value['url']}\">{$value['url']}</a></h4>";
			echo '</div>';
		}
?>
            </div>
            <div class="pages"><?=$multi?></div>
        </div>
	    <div class="footer">
	        <div class="footer_content">@2011 lvyou <a href="#">使用前必读</a> <a href="#">旅游用户协议</a> <a href="#">联系我们</a></div>
	    </div>
	    <div class="clear"></div>
	</div>
<script type="text/javascript">
$(document).ready(function() {
	$('.article_fancybox').fancybox({
		'titlePosition'		: 'inside',
		'transitionIn'		: 'none',
		'transitionOut'		: 'none'
	});
})
</script>
</body>
</html>