<?php include 'sep/header.php';?>
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
                        <ul>
                            <?php
                            $class = empty($get_sets['ct']) ? 'left_title' : '';
                            $url = "/search/?q={$get_sets['q']}";
							$url .= !empty($get_sets['cq']) ? "&cq={$get_sets['cq']}" : '';
							echo "<a href=\"{$url}\"><li class=\"{$class}\">全部</li></a>";
                            foreach(array('介绍', '美食', '交通', '气候', '购物') AS $value){
                            	$class = $get_sets['ct'] == $value ? 'left_title' : '';
                            	$url = "/search/?q=".h($get_sets['q']);
                            	$url .= !empty($get_sets['cq']) ? "&cq=".h($get_sets['cq']) : '';
                            	$url .= "&ct={$value}";
                            	echo "<a href='".$url."'><li class=\"{$class}\">".($value == '介绍' ? '文化' : $value)."</li></a>";
                            		
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="right">
				<div id="div_attr_ex">
					<div id="div_attr_ex_photo"></div>
					<div id="div_attr_ex_list"></div>
					<div id="div_attr_ex_attr"></div>
				</div>
				<div id="map_canvas"></div>
				<?=!empty($region_id) ? '<h2 class="'.(empty($user_detail) ? 'notlogin' : 'login').'"><a class="collcetion" href="/search/collect_pre_modify?cate=regi&id='.$region_id.'">收藏regi</a></h2>' : ''?>
				<?=!empty($poi_id) ? '<h2 class="'.(empty($user_detail) ? 'notlogin' : 'login').'"><a class="collcetion" href="/search/collect_pre_modify?cate=poi&id='.$poi_id.'">收藏poi</a></a></h2>' : ''?>
<?php /*
                <div class="right_conter">
                    <div class="right_middle">
                        <div class="right_description">
                            <span><img src="images/tour_13.gif" width="355" height="3" /></span>
                            <h1>描述：</h1>
                            <h2>王牌景观：“黄山归来不看云，九寨归来不看水”，九寨沟的美丽让见过它的人们无不啧啧赞叹。九寨沟以原始的生态环境一尘不染的清新空气和雪山、森林、湖泊组合成神妙、奇幻幽美的自然风光，显现“自然的美，美的自然”被誉为“童话世界”、“人间仙境”。九寨沟的高峰、彩林、翠海、叠瀑和藏族风情被称为“五绝”... </h2>
                        </div>
                        <div class="right_evaluation">
                            <p>景点：</p>
                            <p>特产：</p>
                        </div>
                    </div>
                    <div class="right_whole">
                        <div class="right_img">
                            <div class="right_big"><a href="#"><img src="images/tour_07.gif" alt="#" width="520" height="233" border="0" /></a></div>
                            <div class="right_insets">
                                <ul>
                                    <li><a href="#"><img src="images/tour_08.gif" width="115" height="56" border="0" /></a></li>
                                    <li class="yanse"><a href="#"><img src="images/tour_09.gif" width="115" height="56" border="0" /></a></li>
                                    <li><a href="#"><img src="images/tour_10.gif" width="115" height="56" border="0" /></a></li>
                                    <li class="right_clear"><a href="#"><img src="images/tour_11.gif" width="115" height="56" border="0" /></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="right_map"><img src="images/tour_12.gif" width="533" height="146" /></div>
                    </div>
                </div>
*/
/* relative_keyword */
/*
if(!empty($relative_keyword)){
		echo '<div class="right_travel">';
			echo "<h1>{$title} 相关关键词</h1>";
			echo '<div class="keyword_box">';
			foreach($relative_keyword AS $value){
				echo '<div class="relative_keyword">';
					echo "<a href='/search/?q={$value}'>{$value}</a>";
				echo '</div>';
			}
			echo '<div class="clear"></div>';
			echo '</div>';
		echo '</div>';				
}
*/
if(!empty($relative_keyword)){
	foreach($relative_keyword AS $key => $value){
		echo "<div id='gc_{$key}' class='google_chart'></div>";
		echo "<script type='text/javascript'>";
		echo "
		google.load(\"visualization\", \"1\", {packages:[\"corechart\"]});
		google.setOnLoadCallback(draw{$key}Chart);
		function draw{$key}Chart() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', '关键词');
	        data.addColumn('number', '关联度');
	        data.addRows(".count($value).");";
		foreach($value AS $k => $v){
			echo "data.setValue({$k}, 0, '{$v['keyword2']}');";
			echo "data.setValue({$k}, 1, {$v['relate']});";
		}
		echo "
	        var chart = new google.visualization.ColumnChart(document.getElementById('gc_{$key}'));
	        chart.draw(data, {legend: 'none', width: 450, height: 300, title: '分类{$key} 相关关键词'});
		}";
		echo "</script>";
	}
}
echo '<div class="clear"></div>';
/* article */
if(empty($get_sets['ct'])){
		echo '<div class="right_travel">';
			echo "<h1>{$title} 文章<a class='more' href='/search/article/{$get_sets['q']}'>更多</a></h1>";

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
						$url = $value['originUrl'] ?: $value['url'];
					echo "<h4>原文：<a href=\"{$url}\">{$url}</a></h4>";
					echo '<h2 class="'.(empty($user_detail) ? 'notlogin' : 'login').'"><a class="collcetion" href="/search/collect_pre_modify?cate=arti&id='.$value['_id'].'">收藏该article</a></h2>';
				echo '</div>';
			}
		echo '</div>';
}
?>
<?php 
if(!empty($get_sets['ct'])){
             echo '<div class="right_travel">
                    <h1>'.$title.' 帖士</h1>';

			foreach($note AS $value){
				echo '<div class="travelnotes travel_article">';
					echo '<a href="#'.$value['_id'].'" class="article_fancybox"><div class="right_title contents" >';
						echo utf8_str_cut_off(trim($value['content']), 200);
					echo '</div></a>';
					echo "<div style=\"display: none;\"><div id=\"{$value['_id']}\" style=\"width:800px;height:500px;overflow:auto;\">".nl2br($value['content'])."</div></div>";
					echo "<h3><em>标题：{$value['title']}</em>";
					if(!empty($value['author'])){
						echo "<em>作者：{$value['author']}</em>";
					}
						echo "<em>时间：".date("Y-m-d",strtotime($value['publishDate']))."</em></h3>";
						echo "<h3>相关关键词:  ".note_tag_link($value['keywords'], 'keyword_link')."</h3>";
//						echo "<h3>pid:  ".$value['pid']."</h3>";
						echo "<h3>category:  ".$value['category']."</h3>";
						echo "<h3>mongo_id:  ".$value['_id']."</h3>";
						$url = $value['originUrl'] ?: $value['url'];
					echo "<h4>原文：<a href=\"{$url}\">{$url}</a></h4>";
					echo '<h2 class="'.(empty($user_detail) ? 'notlogin' : 'login').'"><a class="collcetion" href="/search/collect_pre_modify?cate=note&id='.$value['_id'].'">收藏该note</a></h2>';
				echo '</div>';
			}
			echo '</div>';
}
?>
                
                
                
                
                
<?php /* 
                <div class="right_travel">
                    <h1>九寨沟微博</h1>
                    <div class="travelnotes">
                        <div class="weibo_image"><img src="images/tour_15.gif" width="44" height="44" /></div>
                        <div class="weibo_title"><a href="#">nxzy RT @buqueding: </a> 朋友移民澳洲8年了，在悉尼，刚才电话我。明年6月携国内家人游九寨沟，邀我一同前往。我说“好！如果我还在世。人生无常，也许到时我不在了，你淡定。”<p><a href="#">Twitter</a> - 1天前</p>
                        </div>
                    </div>
                    <div class="travelnotes remove">
                        <div class="weibo_image"><img src="images/tour_19.gif" width="44" height="44" /></div>
                        <div class="weibo_title">
                            <a href="#">nxzy RT @buqueding: </a> 朋友移民澳洲8年了，在悉尼，刚才电话我。明年6月携国内家人游九寨沟，邀我一同前往。我说“好！如果我还在世。人生无常，也许到时我不在了，你淡定。”<p><a href="#">Twitter</a> - 1天前</p>
                        </div>
                    </div>
                </div>
                <div class="right_travel">
                <h1>九寨沟新闻</h1>
                    <div class="travelnotes_news">
                        <ul>
                            <li>・【旅游新闻】<a href="#">国航新开北京至雅典航线 赴希腊旅游更方便</a> (04/26 19:21)</li>
                            <li>・【旅游新闻】<a href="#">今年“五一游”热得有点慢 游客出行日趋理性</a> (04/26 14:23)</li>
                            <li>・【旅游新闻】<a href="#">地震后内外游客锐减　日本旅游业步入严冬</a> (04/26 14:22)</li>
                            <li>・【旅游新闻】<a href="#">旅行社擅加购物次数 加一次游客获10%赔偿</a> (04/26 14:20)</li>
                            <li>・【旅游新闻】<a href="#">团队旅游陷阱多　旅游局公布典型旅游案例</a> (04/21 14:35)</li>
                            <li>・【旅游新闻】<a href="#">网友自创美食地图　全国名吃一览无遗</a> (04/21 14:35)</li>
                            <li>・【旅游新闻】<a href="#">我国出境旅游产业运行呈现五大趋势</a> (04/21 11:55)</li>
                            <li>・【旅游新闻】<a href="#">团队旅游陷阱多　旅游局公布典型旅游案例</a> (04/21 14:35)</li>
                        </ul>
                    </div>
                </div>
*/?>
            </div>
            <div><a id="test" href="http://mw2.google.com/mw-panoramio/photos/medium/27557663.jpg" style="display:none;">test</a></div>
            <div class="pages"><?=$multi?></div>
            <div class="map_pic_area" style="display:none;"></div>
        </div>
	    <div class="footer">
	        <div class="footer_content">@2011 lvyou <a href="#">使用前必读</a> <a href="#">旅游用户协议</a> <a href="#">联系我们</a></div>
	    </div>
	    <div class="clear"></div>
	</div>
<script type="text/javascript">
$(document).ready(function() {
	$("#textbox").autocomplete("/search/query_relative/", {
		minchars: 1,
		max: 9,
		delay: 0,
		mustmatch: true,
		matchcontains: false,
		scrollheight: 220,
		selectfirst: false,
//		width: 260,
//		scroll: true,
		formatitem: function(data, i, total) {
			if(data[1]=="a"){
				return '<strong>'+data[0]+'</strong>';
			}
			return data[0];
		}
	});
});

function creat_collection(id, cate){
	$.ajax({
		'type': "GET",
		'url': "/user/creat_collection/",
		'dataType': 'text',
		'data': 'cate=' + cate + '&id=' + id,
		'success': function(sig){
			if(sig == 'notlogin'){
				window.location = '/user/login';
			}
			if(sig == 'collected'){
				alert('您已经收藏过此项目，请勿再次收藏');
			}
			if(sig == 'success'){
				alert('收藏成功');
			}
		}
	});
}
</script>
<script type="text/javascript">
var map;

$(document).ready(function(){
	var geocoder;
	geocoder = new google.maps.Geocoder();
	var myOptions = {
		zoom: 14,
		panControl: true,
		zoomControl: true,
		scaleControl: true,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	}
	map = new google.maps.Map(document.getElementById("map_canvas"), myOptions); 

	address = '<?=$get_sets['q']?>';
    geocoder.geocode( { 'address': address}, function(results, status) {
		if(status == google.maps.GeocoderStatus.OK){
			map.setCenter(results[0].geometry.location);
			var marker = new google.maps.Marker({
				map: map, 
				position: results[0].geometry.location,
				title:'<?=$get_sets['q']?>'
			});
			google.maps.event.addListener(marker, 'click', function() {
				infowindow.open(map,marker);
			});
		}else{
			alert("Geocode was not successful for the following reason: " + status);
		}
	});
	
	google.maps.event.addListener(map, 'tilesloaded', function(){
		var bounds = map.getBounds();
		var southWest = bounds.getSouthWest();
		var northEast = bounds.getNorthEast();
		var img_options = {
			order: "popularity",
			set: "public",
			from: "0",
			to: "20",
			minx: southWest.lng(),
			miny: southWest.lat(),
			maxx: northEast.lng(),
			maxy: northEast.lat(),
			size: "medium",
			mapfilter: true,
			callback: 'addmarker',
		};
		var url = "http://www.panoramio.com/map/get_panoramas.php?";
		$.each(img_options, function(k, v){
			url += k+'='+v+'&';
		});
//		var script = $('<script><\/script>');
//		script
//			.attr('src', url);
//		$('head').append(script);
	    var script = document.createElement('script');
	    script.setAttribute('src', url);
	    script.setAttribute('id', 'jsonScript');
	    script.setAttribute('type', 'text/javascript');
	    document.documentElement.firstChild.appendChild(script);
	});

	var contentString = '<?=$map_marker_desc?>';

	var infowindow = new google.maps.InfoWindow({
		content: contentString
	});
});
</script>
<script type="text/javascript">

var markers = {};

function addmarker(json){
	var marker;
	$.each(json.photos, function(k, v){
		if(!markers[v.photo_id]){
			v.zindex = k;
			setMarkers(v);
			markers[v.photo_id] = true;
		}
	});
	$('.img_box').fancybox({
		'titlePosition'		: 'inside',
		'transitionIn'		: 'none',
		'transitionOut'		: 'none'
	});
}
  
function setMarkers(panoItem) {
	// Add markers to the map
	var m_icon = new google.maps.MarkerImage(
		//img url
		panoImg(panoItem.photo_id, 'mini_square'),
		// This marker is 20 pixels wide by 32 pixels tall.
		null, //  new google.maps.Size(20, 20),
		// The origin for this image is 0,0.
		null, //new google.maps.Point(0,0),
		// The anchor for this image is the base of the flagpole at 0,32.
		null, //new google.maps.Point(0, 32),
		// 缩放
		new google.maps.Size(20, 20)
	);
	//var shadow = new google.maps.MarkerImage(
		//'images/beachflag_shadow.png',
		// The shadow image is larger in the horizontal dimension
		// while the position and offset are the same as for the main image.
		//new google.maps.Size(37, 32),
		//new google.maps.Point(0,0),
		//new google.maps.Point(0, 32)
	//);
	// Shapes define the clickable region of the icon.
	// The type defines an HTML <area> element 'poly' which
	// traces out a polygon as a series of X,Y points. The final
	// coordinate closes the poly by connecting to the first
	// coordinate.
	//var shape = {
		//coord: [1, 1, 1, 20, 18, 20, 18 , 1],
		//type: 'poly'
	//};
	
	var img_box = '<a href="#key'+ panoItem.zindex +'" id="img'+ panoItem.zindex +'" class="img_box"></a>';
	var content = 
   		"<div id='key"+ panoItem.zindex +"'>" +
	    	"<p><a href='http://www.panoramio.com/' target='_blank'>" +
	    	"<img src='http://www.panoramio.com/img/logo-small.gif' border='0' width='119px' height='25px' alt='Panoramio logo' /><\/a></p>" +
	    	"<a id='photo_infowin' target='_blank' href='" + panoItem.photo_url + "'>" +
	    	"<img border='0' width='" + panoItem.width + "' height='" + panoItem.height + "' src='" + panoItem.photo_file_url + "'/><\/a>" + //src='" + panoImg(panoItem.photo_id, 'original') + "'
	    	"<div style='overflow: hidden; width: 240px;'>" +
	    	"<p><a target='_blank' class='photo_title' href='" + panoItem.photo_url +
	    	"'><strong>" + panoItem.photo_title + "<\/strong><\/a></p>" +
	    	"<p>Posted by <a target='_blank' href='" + panoItem.owner_url + "'>" +
	    	panoItem.owner_name + "<\/a></p><\/div>" +
		"<\/div>";
	$('.map_pic_area').append(img_box + content);
    var myLatLng = new google.maps.LatLng(panoItem.latitude, panoItem.longitude);
 	var marker = new google.maps.Marker({
        position: myLatLng,
        map: map,
		//shadow: shadow,
        icon: m_icon,
		//shape: shape,
        title: panoItem.photo_title,
        zIndex: panoItem.zindex,
    });
 	var infowindow = new google.maps.InfoWindow({
		content: content,
	});
 	google.maps.event.addListener(marker, 'click', function() {
		$('#img' + panoItem.zindex).click();
	});
}
  
function panoImg(photoId, imgType) {
	return 'http://www.panoramio.com/photos/' + imgType + '/' + photoId + '.jpg';
}
</script>
<script type="text/javascript">
	var sand = {
			'tag': '<?=$get_sets['q'] ?>',
		};  
	var sandRequest = new panoramio.PhotoRequest(sand);  
	var attr_ex_photo_options = {    
		'width': 450,    
		'height': 300,    
		'attributionStyle': panoramio.tos.Style.HIDDEN
		};  
	var attr_ex_photo_widget = new panoramio.PhotoWidget('div_attr_ex_photo', sandRequest, attr_ex_photo_options);  
	
	var attr_ex_list_options = {    
		'width': 450,    
		'height': 70,    
		'columns': 6,    
		'rows': 1,    
		'croppedPhotos': true,    
		'disableDefaultEvents': [panoramio.events.EventType.PHOTO_CLICKED],    
		'attributionStyle': panoramio.tos.Style.HIDDEN
		};  
	var attr_ex_list_widget = new panoramio.PhotoListWidget('div_attr_ex_list', sandRequest, attr_ex_list_options);  
	
	var attr_ex_attr_options = {'width': 450};  
	
	var attr_ex_attr_widget = new panoramio.TermsOfServiceWidget('div_attr_ex_attr', attr_ex_attr_options);  
	
	function onListPhotoClicked(event) {    
		var position = event.getPosition();    
		if (position !== null) attr_ex_photo_widget.setPosition(position);  
		}  
	panoramio.events.listen( attr_ex_list_widget, panoramio.events.EventType.PHOTO_CLICKED,  function(e) { onListPhotoClicked(e); });  
	attr_ex_photo_widget.enablePreviousArrow(false);  
	attr_ex_photo_widget.enableNextArrow(false);  
	attr_ex_photo_widget.setPosition(0); 
	attr_ex_list_widget.setPosition(0);
</script>
<script type="text/javascript">
$(document).ready(function() {
	$('.article_fancybox').fancybox({
		'titlePosition'		: 'inside',
		'transitionIn'		: 'none',
		'transitionOut'		: 'none'
	});
	//fancy窗口高度
//	$(window).resize(function(){
//		fancyoptions.width = $(window).width() * 0.8 + 'px',
//		fancyoptions.height = $(window).height() * 0.7 + 'px',
//	});
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
	$('h2.notlogin a').click(function(){
			window.location = '/user/login';
	});
	$('h2.login a').fancybox(fancyoptions);
})
</script>
</body>
</html>