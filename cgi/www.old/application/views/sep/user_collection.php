<?php include 'header.php'; ?>
</div>
<!--end of #box-->

<div class="map_left">
	<div class="map_center">
		<h1>九寨沟</h1>
		<input name="" type="button"  value="修改" class="xiugai"/>
		<h4>
			103 次浏览<br />
			于 2008 年 6 月 2 日创建 · 作者 qi · 6 小时前已更新
		</h4>
	</div>
	<div class="map_poi">
		<h1>POI</h1>
		<ul>
			<h2>安徽省</h2>
			<li><span><img src="/images/sep/map_01.jpg" width="16" height="24" /></span><a href="#">屯溪汽车站</a></li>
			<li><span><img src="/images/sep/map_01.jpg" width="16" height="24" /></span><a href="#">黄山国际青年旅</a>馆<br /><b><a href="#">安徽省黄山市市辖区 北海路 更多信息>></a></b></li>
			<li><span><img src="/images/sep/map_01.jpg" width="16" height="24" /></span><a href="#">花山谜窟</a><br /><b><a href="#">sdadf</a></b></li>
			<li><span><img src="/images/sep/map_01.jpg" width="16" height="24" /></span><a href="#">屯溪老街</a><br />
			<b><a href="#">晚上九点去happy....</a></b></li>
			<li><span><img src="/images/sep/map_01.jpg" width="16" height="24" /></span><a href="#">云谷索道</a><br />
			<b><a href="#">安徽省黄山市市辖区 更多信息>></a></b></li>
		</ul>
	</div>
	<div class="map_article">
		<h1> Article</h1>
		<ul>
			<li><a href="#">[北京4日线路设计]及[北京超值小吃推荐] 2010-04-05</a></li>
			<li><a href="#">我眼中的北京 2010-04-12</a></li>
			<li><a href="#">九寨沟五彩神仙池 2011-09-01</a></li>
		</ul>
	</div>
</div>
<div class="map_img" id="map_area">
<!--	<img src="/images/sep/ditu.jpg" width="100%" />-->
</div>
<div class="clear"></div>

<script type="text/javascript">
var test = {};
	
$(window).resize(resizeMap);
//根据窗口高度调整地图区域高度
function resizeMap(){
	var mapHeight = Math.max($(window).height() - $('#header').height(), $('.map_left').height()) - 5;
	$('.map_img').css('height', mapHeight + 'px');
}

content = '<div><div><div class="msedit"><table class="iwspan"><tbody><tr><td><table class="inputField"><tbody><tr><td class="label">标题</td><td><input type="text" maxlength="250" class="title" dir="ltr"></td><td class="stylecol"><div id="msiwsi" class="icon" title=""><img style="width: 32px; height: 32px; -moz-user-select: none; border: 0px none; padding: 0px; margin: 0px;" src="http:////maps.gstatic.com/mapfiles/ms2/micons/blue-dot.png"></div></td></tr></tbody></table><table class="inputField"><tbody><tr><td class="label">说明</td><td class="tabs"><span class="stab">纯文本</span><span class="stab">&nbsp;-&nbsp;</span><span class="lk">Rich text</span><span class="stab">&nbsp;-&nbsp;</span><span class="lk">修改 HTML</span></td></tr></tbody></table></td></tr></tbody></table><div><div id="rtfield" class="textField description" style="display: none;"></div><textarea class="textField description" dir="ltr"></textarea><div style="font-size: 1%; width: 264px; height: 1px;"></div></div><div class="msiwpd" style=""><table width="100%"><tbody><tr><td width="1000"><span class="msiwpdeheader">地点详情</span></td><td class="msiwpdecol"><a href="javascript:void(0)" class="msiwpdeedit"><nobr>修改</nobr></a></td></tr></tbody></table><div class="iw msiwpde"><table width="100%"><tbody><tr><td width="1000"><span>天坛餐厅</span><br><span>50号 天坛东路</span><br><span>崇文区, Beijing, 北京, China, 100-050</span><br><span>010-81191011</span><br><span class="msiwpdehp">tiantanpark.com</span><br></td><td class="msiwpdphotocol"></td></tr></tbody></table></div></div><a href="javascript:void(0)" class="msiwpdhidden" style="display: none;">显示驾车路线</a><div class="mstotaldistance mstotaldistancebot" style="display: none;"></div><div style="display: none;"><table width="100%"><tbody><tr><td><span class="msiwddetitle">驾车路线</span></td><td class="msiwpdecol"><a href="javascript:void(0)" class="msiwpdeedit">隐藏</a></td></tr></tbody></table><div class="msiwddecontain msiwddesuccess"></div></div><table><tbody><tr><td class="navLeft"><span id="msiwdl" class="lk">删除</span></td><td class="navRight"><span id="mscb" class="lk">取消</span></td><td class="navRight"><button class="kd-button">确定</button></td></tr></tbody></table></div><div class="msstyle" style="display: none; width: 301px;"></div></div></div>';
$(document).ready(function(){
	resizeMap();
	
	var mapOptions = {
		id: 'map_area',
		set: {panControl: true, zoomControl: true, scaleControl: true},
	}
	draw_map(mapOptions);
	var markerOptions = {
		id: 0,
		set:{
	//		title: '<?=$get_sets['q']?>',
	//		content: '<?=$map_marker_desc?>',
			content: content,
	//		address: '<?=$get_sets['q']?>',
			address: '北京',
			position: null,
		},
	}
	draw_marker(markerOptions);
	draw_pano_layer();
});
</script>
</body>
</html>