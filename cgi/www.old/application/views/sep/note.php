<?php include 'header.php'; ?>

<div class="mbread">
	<div class="mbred_page">
		<span><?=$crumbs?></span>
	</div>
</div>
<?php include 'left_category.php';?>



<div id="mail_right">
<?php if(!empty($poi_cate_arr)){?>
	<div class="right_middle">
		<div class="right_description rw_jzg">
		    <h1><?=$name?><?=$poi_category[$category]?></h1>
<?php 
		$draw_marker_id = array();
		$count = 0;
		
    	foreach($poi_cate_arr AS $key => $value){
    		$draw_marker_id[] = $value[0]['_id'];
    		$wiki_tmp = get_wiki_content($value[0]['name']);
    		echo '
    		<ol>
				<dt><a href="javascript:void(0)" onclick="javascipt:setMarkerCenter(\''.$value[0]['_id'].'\');"><img src="'.google_map_icon_url($count++).'" /></a></dt>
				<div class="jt_jzg">
					<dd class="wz_jzg">'.$value[0]['name'].'</dd>
<!--					<dd>0559 293 4144</dd>-->
						<dd>'.utf8_str_cut_off($wiki_tmp['content'], 70).'</dd>
				</div>
			</ol>
    		';
    	}
?>

<?php /* ?>		    
		    
    <?php if(!empty($poi_cate_arr['airport'])){?>
	        <ol>
				<dt><img src="/images/sep/tb_04.jpg" width="22" height="36" /></dt>
				<div class="jt_jzg">
					<dd class="wz_jzg"><?=$poi_cate_arr['airport'][0]['name']?></dd>
<!--					<dd>0559 293 4144</dd>-->
<!--					<dd>黄山屯溪机场（IATA：TXN，ICAO：ZSTX）是一个位于中国安徽黄山的民用机场。</dd>-->
				</div>
			</ol>
	<?php }//airport?>
	<?php if(!empty($poi_cate_arr['train'])){?>
			<ol>
				<dt><img src="/images/sep/tb_01.jpg" width="22" height="36" /></dt>
				<div class="jt_jzg">
					<dd class="wz_jzg"><?=$poi_cate_arr['train'][0]['name']?></dd>
<!--					<dd>安徽省黄山市屯溪区前园北路</dd>-->
				</div>
			</ol>
	<?php }//train?>
	<?php if(!empty($poi_cate_arr['subway'])){?>
			<ol>
				<dt><img src="/images/sep/tb_02.jpg" width="22" height="36" /></dt>
				<div class="jt_jzg">
					<dd class="wz_jzg"><?=$poi_cate_arr['subway'][0]['name']?></dd>
<!--					<dd>0559 256 6666 </dd>-->
<!--					<dd>安徽省黄山市屯溪区</dd>-->
				</div>
			</ol>
	<?php }//subway?>
	<?php /*
			<ol>
				<dt><img src="/images/sep/tb_03.jpg" width="22" height="36" /></dt>
				<div class="jt_jzg">
					<dd class="wz_jzg">黄山汽车客运总站</dd>
					<dd>0559 293 4144</dd>
					<dd>安徽省黄山市屯溪区齐云大道</dd>
				</div>
			</ol>
	*/?>
		</div>
		<div class="right_img poi_mapst">
			<div class="right_map" id="note_map" style="width:500px;height:372px;"></div>
		</div>
<script type="text/javascript">
	var mapOptions = {
		id: 'note_map',
		set: {panControl: true, zoomControl: true, scaleControl: true, zoom: 13},
	}
	draw_map(mapOptions);

	var markerOptions = {
		id: 0,
		set:{
			title: '<?=$name?>',
			address: '<?=$name?>',
			pano: false,
		},
	}
	draw_marker(markerOptions, {display:false});

	var geo_arr = <?=json_encode($geo_arr)?>;

	var draw_marker_id = <?=json_encode($draw_marker_id)?>;

	var count = 0;
	$.each(draw_marker_id, function(k, v){
		var set = geo_arr[v];
		set.iconUrl = googleMapIcon(count++);
		set.shadowUrl = new google.maps.MarkerImage(googleMapIconShadow(), null, null,  new google.maps.Point(10,34));
		draw_marker({id:v,set:set}, {setCenter:false});
	})
</script>
	</div>
<?php }else{
	echo '<style>.rigtop{margin:0;}</style>';
}?>
	<div class="right_center rigtop">
		<div class="right_zhishu right_yjj">
			<div class="right_youji">
				<div class="right_travel youji_traf">
					<h1><?=$name.$poi_category[$category]?>攻略</h1>
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
					foreach($note AS $key => $value){
						$url = $value['originUrl'] ?: $value['url'];
						$tmp_content = $highlighting[$value['_id']]['content']['str'] ?: utf8_str_cut_off($value['content'], 100);
						//utf8_str_cut_off(trim(strip_tags($value['content'])), 100)
						$tmp_title = !empty($highlighting[$value['_id']]['title']['str']) ? $highlighting[$value['_id']]['title']['str'] : utf8_str_cut_off(trim(strip_tags($value['title'])), 30);
						echo '
						<div class="travelnotes '.($key == (count($note) - 1 ) ? 'remove' : '').'">
							<h2>'.$tmp_title.'</h2>
							<span>'.(date('Y-m-d', strtotime($value['publishDate']))).'</span>'.
//							<h3>作者：<a href="#">无</a>　 来源：无　 消费浏览：0　 回复：0 </h3>
							'<div class="right_title">'.$tmp_content.'</div>
							<h4>'.$url.' 约'.mb_strlen($value['content'], 'utf-8').'字-<a class="info_preview" href="/search/article_preview/'.$value['_id'].'">预览</a></h4>
						</div>';
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