<?php include 'sep/header.php';?>


<div id="wrapper">
	<div id="main_left" class="fleft">
		<ul class="left_tree">
<?php 
	foreach($collect_res AS $key => $value){
		echo '
			<li>
				<span class="hit_area"></span>
				<span class="tree_mother">'.$key.'</span>
				<ul class="tree_child">';
		foreach($value AS $k => $v){
			if($key == 'POI' || $key == 'Region'){
				echo '<li onclick="javascript:show_marker(\''.$v['_id'].'\')">'.($v['name'] ?: $v['title']).' '.$v['publishDate'].'</li>';
			}else{
				echo '<a class="fancybox" href="#'.$v['_id'].'"><li>'.($v['name'] ?: $v['title']).' '.$v['publishDate'].'</li></a>';
				echo "<div style=\"display: none;\"><div id=\"{$v['_id']}\" style=\"width:800px;height:500px;overflow:auto;\">".nl2br($v['content'])."</div></div>";
			}
		}
		echo '
				</ul>
			</li>';
	}
?>
		</ul>
	</div>
	<div id="main_right" class="fright">
	</div>
	<div class="clear"></div>
</div>

<script type="text/javascript">
	//目录树展开
	$(".left_tree .hit_area").click(function(){
		$(this).toggleClass("expand");
		$(this).siblings("ul.tree_child").toggle();
	}); 
	//窗口高度
	$(window).resize(function(){
		var body_height = Math.max($(window).height(), $('#main_left').height(), $('#main_right').height()) - 35;
		$('body').css('height',body_height + 'px');
	});
	var body_height = Math.max($(window).height(), $('#main_left').height(), $('#main_right').height()) - 35;
	$('body').css('height',body_height + 'px');
	
	//画地图
	var geocoder;
	var markers = new Array();
	var map;
	 
	geocoder = new google.maps.Geocoder();
	
	var myOptions = {
		zoom: 5,
		panControl: true,
		zoomControl: true,
		scaleControl: true,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	map = new google.maps.Map(document.getElementById("main_right"), myOptions); 
	
	var marker_data = <?=json_encode($marker)?>;
	
	$.each(marker_data, function(key, value){
		marker_data[key].marker = add_marker(value, key);
	});
	
	function add_marker(marker_detail, id){
		var res = new Array();
		var result;
		var marker_options = {
			map: map, 
		}
		if(marker_detail.title != ''){
			marker_options.title = marker_detail.title
		}
		if(marker_detail.position != null){
			res.lt_lg = new google.maps.LatLng(marker_detail.position.lt, marker_detail.position.lg);
			marker_options.position = res.lt_lg;
			res.marker = new google.maps.Marker(marker_options);
			map.setCenter(res.lt_lg);
			if(marker_detail.content != ''){
				res.infowindow = new google.maps.InfoWindow({
					content: marker_detail. content,
				});
				google.maps.event.addListener(res.marker, 'click', function() {
					infowindow.open(map, res.marker);
				});
			}
			markers[id] = res;
		}else{
		    geocoder.geocode( { 'address': marker_detail.address }, function(results, status) {
				if(status == google.maps.GeocoderStatus.OK){
					res.lt_lg = results[0].geometry.location;
				}else{
					markers[id] = false;
				}
				marker_options.position = res.lt_lg;
				res.marker = new google.maps.Marker(marker_options);
				map.setCenter(res.lt_lg);
				if(marker_detail.content != ''){
					res.infowindow = new google.maps.InfoWindow({
						content: marker_detail. content,
					});
					google.maps.event.addListener(res.marker, 'click', function() {
						res.infowindow.open(map, res.marker);
					});
				}
				markers[id] = res;
			});
		}
	}
	
	var opened_info = '';//打开新的info 之前先要关闭之前的info
	
	function show_marker(id){
		if(opened_info != ''){
			markers[opened_info].infowindow.close(map, markers[opened_info].marker);
		}
		map.setCenter(markers[id].lt_lg);
		map.setOptions({zoom:15});
		markers[id].infowindow.open(map, markers[id].marker);
		opened_info = id;
	}

	$('.fancybox').fancybox({
		'titlePosition'		: 'inside',
		'transitionIn'		: 'none',
		'transitionOut'		: 'none'
	});

</script>

</body>


</html>