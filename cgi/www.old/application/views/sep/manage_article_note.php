<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>旅游景区首页</title>
<!--	css-->	
	<link href="/style/reset.css" rel="stylesheet" type="text/css" />
	<link href="/style/main.css" rel="stylesheet" type="text/css" />
<!--	css-->	
<!--	jquery-->	
	<script type="text/javascript" src="/js/jquery.min.js"></script>
<!--	jquery-->	
<!--	fancybox-->
	<link rel="stylesheet" type="text/css" href="/fancybox/jquery.fancybox-1.3.4.css" media="screen"/>
	<script type="text/javascript" src="/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
	<script src="/fancybox/jquery.mousewheel-3.0.4.pack.js" type="text/javascript"></script>
<!--	fancybox-->
<!--autocomplete-->
	<link href="/style/autocomplete.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="/js/autocomplete.js"></script>
<!--autocomplete-->

	<script type="text/javascript">
		function deleteEle(element) {
			$(element).parents('div.travelnotes').remove();
		}
		
		function toDel(articleId, element) {
			url = "/manage/delete/" + articleId;
			$.ajax(
					{
						url:url,
						type:"POST",
						success:function() {
									alert('删除成功。');
									deleteEle(element)
								},
						error:function(XMLHttpRequest, textStatus, errorThrown) {
								alert('删除失败');
							}
					}
				);
			
		}
	</script>
</head>

<body>
<div id="header">

	<div class="headcont">
		<div class="search">
			<div class="logo"><img src="/images/sep/header_img_04.jpg" width="189" height="20" /></div>
			<div class="searchs">
				<form id="search_box" action="/manage/search" method="get" >
					<input id="query" name="q" type="text" class="searfont" />
					<a id="query_button" class="searimg" onclick="javascript:$('#search_box').submit();"></a>
				</form>
			</div>
		</div>

		<div class="clear"></div>
	</div>
	
</div>
<!--主要内容-->
<div id="main">

<div id="main_left">
	<div class="left_whole">
		<div class="borderimg">
			<img src="/images/sep/tour_01.jpg" />
		</div>
		<div class="left_content">

		</div>
		<div class="borderimg">
			<img src="/images/sep/tour_02.jpg" />
		</div>
	</div>
</div>
<div id="mail_right">
	<div class="right_center youji_clear">
		<div class="right_zhishu">
			<div class="right_youji">
				<div class="right_travel youji_clear">
					<h1><?=$get['q']?>游记</h1>
<?php /*					<div class="travelnotes"> //最后一个travelnote 需要带 remove class
						<h2>九寨沟之旅</h2>    
						<span>2011-08-30</span>
						<h3>作者：<a href="#">艺佳妈</a>　 来源：爱购网|生活　 消费浏览：9345　 回复：2526 </h3>
						<div class="right_title">
							九寨沟的风景很美，去的时候还能看到雪景。从绵阳到九寨沟的路程很长，开车要8小时左右。九寨沟的海拔还可以，景区做车也很方便，游玩起来比较舒服。黄龙的海拔3500，去的时候还下雪，高原反应很明显，身体不好的人就量力而行...
						</div>
						<h4>www.265bbs.com/thread-252188-1-1.htm...  约2504字- <a href="#">收藏</a>- <a href="#">预览</a></h4>
					</div>
*/ ?>
					<?php 
					foreach($result AS $key => $value){
						echo '
						<div class="travelnotes '.($key == (count($result) - 1 ) ? 'remove' : '').'">
							<h2>'.$value['title'].'</h2>
							<span>'.(date('Y-m-d', strtotime($value['publishDate']))).'</span>
							<h3>作者：<a href="#">无</a>　 来源：无　 消费浏览：0　 回复：0 </h3>
							<div class="right_title">'.utf8_str_cut_off($value['content'], 100).'</div>
							<h4>'.($value['originUrl'] ?: $value['url']).'...  约'.mb_strlen($value['content'], 'utf-8').'字-  <a href="#'.$value['_id'].'" class="preview">预览</a>- <a href="#" onclick="javascript:toDel(\''. $value['_id'] . '\', this);" >删除</a></h4>
						</div>';//<a href="/search/collection?id='.$value['_id'].'&cate='.$category.'" target="_blank">预览</a>
						echo '
						<div style="display:none;">
							<div id="'.$value['_id'].'"><h3>'.$value['title'].'</h3><br/>'.nl2br($value['content']).'</div>
						</div>
						';
					}
					
					?>
				</div>
			</div>
		</div>

	</div>
</div>
<div class="pages"><?=$multi?></div>

	<div class="clear"></div>
<!--end of box-->
</div> 
<!--主要内容结尾-->

	<div class="footer">
		<div class="footer_content">@2011 lvyou <a href="#">使用前必读</a> <a href="#">旅游用户协议</a> <a href="#">联系我们</a></div>
	</div>
	<div class="map_pic_area"></div>
<!--js-->
<script type="text/javascript">
$(document).ready(function() {

	$('.preview').fancybox(fancyoptions);
	var fancyoptions = {
		'overlayOpacity': 0.6,
		'width': $('body').width() * 0.8,
		'height': $('body').height() * 0.7,
		'autoDimensions': true,
		'changeSpeed': 10,
		'type': 'iframe',
		'resize': true,
		'padding': 10,
		'margin': 10,
		'modal': false,
		'scrolling': false,
	}
	$("#query").autocomplete("/search/query_relative/", {
		minchars: 1,
		max: 9,
		delay: 0,
		mustmatch: true,
		matchcontains: false,
		scrollheight: 220,
		selectFirst: false,
//		width: 260,
//		scroll: true,
		formatitem: function(data, i, total) {
			if(data[1]=="a"){
				return '<strong>'+data[0]+'</strong>';
			}
			return data[0];
		}
	});
	$('a.pre_view').click(function(){
		var data = {
			id: $(this).attr('id'),
			cate: $(this).attr('cate'),	
		};
		$.ajax({
			'type': "GET",
			'url': "/search/article_note/",
			'dataType': 'html',
			'data': data,
			'success': function(res){
				alert(res);
			}
		});
	});
});
</script>
</body>
</html>