<?php include 'header.php'; ?>

<div class="mbread">
	<div class="mbred_page">
		<span><?=$crumbs?></span>
	</div>
</div>
<?php include 'left_category.php';?>
<div id="mail_right">
	<div class="right_middle">
		<div class="right_description">
			<h1><?=$name?></h1><div class="collection"><a href="#"><img src="/images/sep/tour_07.jpg" width="57" height="21" border="0" /></a></div>
			<h4>
				<?=!empty($region['desc']) ? $region['desc'].'</br>' : '' ?>
				<?=$region['website'] ?: '' ?>
			</h4>
<?php if(!empty($relate['description'])){?>
			<h2>评价</h2>
			<h3>
<?php 
			foreach($relate['description'] AS $key =>  $value){
				if($key > 10){break;}
				echo '<a href="/search/article/'.$region['_id'].'?q2='.rawurlencode($value['keyword']).'">'.$value['keyword'].'</a>		';
			}

?>
			</h3>
<?php }?>
			<h2>描述</h2>
			<h4>
				<?=utf8_str_cut_off($wiki['content'], 150)?>
				<br/>
				<a class="info_preview" href="/search/wiki_preview/<?=$name?>"><span>查看更多</span></a>
			</h4>
		</div>
		<div class="right_img">
			<div class="right_map" id="map_area" style="width:539px;height:330px;">
	<!--			<img src="/images/sep/tour_06.jpg" width="539" height="330" />-->
			</div>
			<div class="right_content">
	<?php 
			foreach($map_category AS $key => $value){
				echo '<input name="'.$key.'" type="checkbox" value="" class="choose" '.(empty($geo_arr[$key]) && $key != 'pano' ? 'disabled="disabled "' : '').' /><span>'.$value.'</span>';
			}
	?>
			</div>
		</div>
<script type="text/javascript">
$(document).ready(function(){

	var geo_arr = <?=json_encode($geo_arr)?>;
	
	$('input.choose').click(function(){
		var category = $(this).attr('name');
		$(this).toggleClass('selected');
		if($(this).hasClass('selected')){
			if(category == 'pano'){
				draw_pano(false);
			}else{
				$.each(geo_arr[category], function(id, set){
					set.iconUrl = get_map_icon(category);
					draw_marker({id:id,set:set}, {setCenter:false});
				})
			}
		}else{
			if(category == 'pano'){
				remove_pano_marker();
			}else{
				$.each(geo_arr[category], function(id, set){
					clean_marker(id);
				})
			}
		}
	});
	
	var mapOptions = {
		id: 'map_area',
		set: {panControl: true, zoomControl: true, scaleControl: true, zoom: 10},
	}
	draw_map(mapOptions);

	var markerOptions = {
		id: 0,
		set:{
			title: '<?=$name?>',
			address: '<?=$name?>',
			position: <?=!empty($geo) ? json_encode($geo) : 'null'?>,
			pano:true,
		},
	}
	draw_marker(markerOptions, {display:false});
});
</script>		
		<div class="clear"></div>
	</div>
	<div class="right_center">
		<div class="right_zhishu">
<?php if(!empty($keyword)){ //关联关键词?>
			<div class="right_guanlian">
				<h1>景点列表</h1>
<?php /*
				<div class="right_js">
					<ul>

						<li class="js_text" chart="0">景点排名</li>
						<li chart="1">交通枢纽</li>
						<li chart="2">美食</li>
						<li chart="3">特产</li>
						<li chart="4">购物中心</li>
						<li chart="5">旅游项目</li>
						<li chart="6">注意事项</li>
			$count = 0;
			foreach($keyword AS $key => $value){
				echo '<li class="'.($count ? '' : 'js_text').'" chart="'.$count ++.'">'.$key.'</li>';
			}
						
					</ul>
				</div>
*/ ?>
				<div class="zhishu_img">
					<div class="key_word_chart" id="chart_wrapper"></div>
<?php 
		$i = $j = 0;
		//for($i = 0; $i<7; $i++){
		$count = 0;
		foreach($keyword AS $key => $value){
			echo '
				<div class="key_word_chart" id="chart_'.$count ++.'">
					<table class="table_chart">
						<tr class="pic">
							<td class="level"></td>';
				for($j = 0; $j<12; $j++){
					$ratio_style = 'height:'.( $value[$j]['rank'] / 1.1 * 140 ).'px;';
					$ratio_box = '<div class="ratio" style="'.$ratio_style.'"></div>';
					echo '<td>'.$ratio_box.'</td>';
				}
			echo '
						</tr>
						<tr>
							<td></td>';
				for($j = 0; $j<12; $j++){
					echo '<td><a href="/search/poi/'.$value[$j]['_id'].'">'.$value[$j]['name'].'</a></td>';
				}
			echo '
						</tr>
					</table>
				</div>';
		}
	
?>
<script type="text/javascript">
	$('.right_js li').click(function(){
		$('.right_js li').removeClass('js_text');
		$('.key_word_chart').css('z-index', -1);
		$(this).addClass('js_text');
		$('#chart_' + $(this).attr('chart') ).css('z-index', 10);
	});
</script>
				</div>
				<div class="clear"></div>
			</div>
<?php }//关联关键词?>
			<div class="right_youji">
				<div class="right_travel">
					<h1><?=$name?>微博</h1>
					<div class="comments-box">
		<?php 
		$count = count($weibo);
			foreach($weibo AS $key => $value){
				/*echo '
					<div class="travelnotes '.($count == $key + 1 ? 'remove' : '').'">
						<h2>'.$value['title'].'</h2>
						<span>'.$value['publishDate'].'</span>
						<div class="right_title">'.utf8_str_cut_off($value['content'], 150).'</div>
						<h4>'.($value['originUrl'] ?: $value['url']).'   <a href="'.($value['originUrl'] ?: $value['url']).'">预览</a></h4>
					</div>';*/
				$content = $highlighting[$value['_id']]['contents']['str'];
				echo '
					<ol '.($count == $key + 1 ? 'class="ing_remove"' : '').'>
						<dt><img src="'.$value['user_img'].'" width="50" height="50" /></dt>
						<dd><a href="#">'.$value['user_name'].'</a> ：'.$content.' <span>('.date("Y-m-d H:i:s", strtotime($value['post_time'])).')</span> </dd>
					</ol>
				';
			}
		?>
					</div>
				</div>
			</div>
		</div>
		<div class="piece">
<?php if(!empty($transportation)) {?>
			<div class="lvyou_xm">
				<h1>到达与离开</h1>
		    	<ul>
<?php 
				foreach($transportation AS $value){
					echo '<li><a href="/search/poi/'.$value['_id'].'">'.$value['name'].'</a></li>';
				}
?>
				</ul>  
			</div>
<?php }//transportation?>
<?php if(!empty($relate['item'])) {?>
			<div class="lvyou_xm">
			    <h1>旅游项目</h1>
		    	<ul>
<?php 
				foreach($relate['item'] AS $key => $value){
					if($key > 10){break;}
					echo '<li><a href="/search/article/'.$region['_id'].'?q2='.rawurlencode($value['keyword']).'">'.$value['keyword'].'</a></li>';
				}
?>
		  		</ul>  
			</div>
<?php }//item?>
<?php if(!empty($relate['food'])) {?>
			<div class="lvyou_xm">
			    <h1>美食</h1>
		    	<ul>
<?php 
				foreach($relate['food'] AS $key => $value){
					if($key > 10){break;}
					echo '<li><a href="/search/article/'.$region['_id'].'?q2='.rawurlencode($value['keyword']).'">'.$value['keyword'].'</a></li>';
					
				}
?>
		  		</ul>  
			</div>
<?php }//food?>
<?php if(!empty($relate['note'])) {?>
			<div class="lvyou_xm">
			    <h1>注意事项</h1>
			    <ul>
			    
<?php 
				foreach($relate['note'] AS $key => $value){
					if($key > 10){break;}
					//'.chr($key + 65).'、。
					echo '<li><a href="/search/article/'.$region['_id'].'?q2='.rawurlencode($value['keyword']).'">'.$value['keyword'].'</a></li>';
				}
?>
				</ul>  
			</div>
<?php }//note?>
<?php if(!empty($region['film'])) {?>
			<div class="movie">
				<h1>相关电影</h1>
<?php 
			foreach(explode(",", $region['film']) AS $key => $value){
				if($key > 10){break;}
				echo '
				<ol>
					<dd><a href="/search/article/'.$region['_id'].'?q2='.rawurlencode($value).'">'.$value.'</a></dd>
				</ol>';
			}
?>
			</div>
<?php }//film?>
		</div>
	</div>
</div>

<div class="pages"><?=$multi?></div>
<?php include 'footer.php'; ?>
