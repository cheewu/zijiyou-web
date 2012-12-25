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