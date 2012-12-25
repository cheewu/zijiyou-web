<?php include 'header.php'; ?>

<div class="mbread">
	<div class="mbred_page">
		<span><?=$crumbs?></span>
	</div>
</div>
<?php include 'left_category.php';?>
<div id="mail_right">
	<div class="right_top">
		<div id="map_area" style="width:860px;height:500px;"></div>
		<div class="right_content attraction_map_category">
<?php 
		$map_category = $ci->config->item('search_region_map_category');
		unset($map_category['attraction']);
		foreach($map_category AS $key => $value){
			echo '<input name="'.$key.'" type="checkbox" value="" class="choose" '.(empty($sub_geo_arr[$key]) && $key != 'pano'  ? 'disabled="disabled "' : '').' /><span>'.$value.'</span>';
		}
?>
		</div>
		<script type="text/javascript">
			var mapOptions = {
				id: 'map_area',
				set: {panControl: true, zoomControl: true, scaleControl: true, zoom: 10},
			}
			draw_map(mapOptions);
			</script>
			<script src="/js/lenth.js" type="text/javascript"></script>
			<script type="text/javascript">
			var homeControlDiv = document.createElement('div');
			var homeControl = new HomeControl(homeControlDiv, '测距');
			var dis_boxDiv = document.createElement('div');
			var dis_box = new HomeControl(dis_boxDiv, '0m');
			var dis_listener;
			homeControl.controlUI.style.backgroundColor = '#FF6600';
			homeControl.controlUI.style.color = 'white';
			homeControl.controlUI.style.borderColor = '#FF6600';
			dis_box.controlUI.style.backgroundColor = '#FF6600';
			dis_box.controlUI.style.color = 'white';
			dis_box.controlUI.style.borderColor = '#FF6600';
			
			google.maps.event.addDomListener(homeControl.controlUI, 'click', function() {
				var status = homeControl.Text.innerHTML;
				if(status == '测距'){
					homeControl.Text.innerHTML = '清除';
					map.controls[google.maps.ControlPosition.TOP_RIGHT].push(dis_boxDiv);
					dis_listener = google.maps.event.addListener(map, "click", function(event){
						add_Marker(event.latLng, dis_box);
				    });
				}else{
					homeControl.Text.innerHTML = '测距';
					map.controls[google.maps.ControlPosition.TOP_RIGHT].pop(dis_boxDiv);
					google.maps.event.removeListener(dis_listener);
					deleteOverlays(dis_box);
				}
			});
			
			homeControlDiv.index = 1;
			map.controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);
			
			var sub_geo_arr = <?=json_encode($sub_geo_arr)?>;
			
			$('input.choose').click(function(){
				var category = $(this).attr('name');
				$(this).toggleClass('selected');
				if($(this).hasClass('selected')){
					if(category == 'pano'){
						draw_pano(true);
					}else{
						$.each(sub_geo_arr[category], function(id, set){
							set.iconUrl = get_map_icon(category);
							draw_marker({id:id,set:set}, {setCenter:false});
						})
					}
				}else{
					if(category == 'pano'){
						remove_pano_marker();
					}else{
						$.each(sub_geo_arr[category], function(id, set){
							clean_marker(id);
						})
					}
				}
			});
			
			var markerOptions = {
				id: 0,
				set:{
					title: '<?=$name?>',
					address: '<?=$name?>',
					position: <?=!empty($geo) ? json_encode($geo) : 'null'?>,
					pano: true,
				},
			}
			draw_marker(markerOptions, {display:false});

			var geo_arr = <?=json_encode($geo_arr)?>;

			var count = 0;
			$.each(geo_arr, function(id, set){
				set.iconUrl = googleMapIcon(count++);
				set.shadowUrl = new google.maps.MarkerImage(googleMapIconShadow(), null, null,  new google.maps.Point(10,34));
				draw_marker({id:id,set:set}, {setCenter:false});
			})
			
		</script>
	</div>
	<div class="right_center rigtop">
		<div class="right_zhishu right_yjj">
			<div class="right_youji">
				<div class="right_travel youji_traf">
					<?php 
					$count = 0;
					foreach($attraction AS $key => $value){
						echo '
						<div class="travelnotes '.($count == (count($attraction) - 1 ) ? 'remove' : '').'">
							<h2><a href="#map_area" onclick="javascipt:setMarkerCenter(\''.$key.'\');"><img src="'.google_map_icon_url($count).'" /></a><a href="/search/poi/'.$value['_id'].'">'.$value['name'].'</a></h2>
							<div class="right_title">'.utf8_str_cut_off(trim(strip_tags($value['desc'])), 100).'</div>
						</div>';
						$count ++;
						// <a href="#">收藏</a>- 
					}
					
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="pages"><?=$multi?></div>
<?php include 'footer.php'; ?>