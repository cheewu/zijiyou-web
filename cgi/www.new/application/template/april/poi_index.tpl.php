<?php include 'header.tpl.php';?>
<div id="middle">
	<div class="classify">
		<?=crumbs($region_id, $poi_id)?>
	</div>
	<div id="middle_left">
		<div class="travel">
			<div class="headline"><?=$region['name']?>:<?=$name?></div>
			<div class="jdpoi">
				<div class="jd_img">
					<img src="<?=$poi_pic ? $poi_pic : ""?>" width="150" height="150" />
<?php /*?>
					<ul>
						<li class="jd_click"><a href="#"><img src="images/attraction_01.jpg" width="40" height="40"></a></li>
						<li><a href="#"><img src="images/attraction_02.jpg" width="40" height="40"></a></li>
						<li><a href="#"><img src="images/attraction_03.jpg" width="40" height="40"></a></li>
						<li><a href="#"><img src="images/attraction_01.jpg" width="40" height="40"></a></li>
						<li class="tu_jd"><a href="#"><img src="images/attraction_02.jpg" width="40" height="40"></a></li>
					</ul>
<?php */?>
				</div>
				<div class="jd_nr" >
					<?=!empty($poi['address']) ? "<h5>地址:{$poi['address']}</h5>" : ""?>
					<?=!empty($poi['opentime']) ? "<h5>开放时间:{$poi['opentime']}</h5>" : ""?>
					<?=!empty($poi['telNum']) ? "<h5>电话:{$poi['telNum']}</h5>" : ""?>
					<?=!empty($poi['website']) ? "<h5>网址:<a href='{$poi['website']}'>{$poi['website']}</a></h5>" : ""?>
					<?=!empty($poi['ticket']) ? "<h5>门票:{$poi['ticket']}</h5>" : ""?>
					<?php //=!empty($poi['traffic']) ? "<h5>交通:{$poi['traffic']}</h5>" : ""?>
<!--					<h5>到达方式: 地铁1号线到天安门东（西）站下</h5>-->
<!--					<h5>类型：主题公园</h5>-->
				</div>
				<?php if(!empty($wiki)) {?>
				<div class="Introduction"><?=utf8_substr_ifneeed(strip_tags(@$wiki['content']), 200, false, '...')?><A href="/wiki/<?=$region_id?>/<?=strval($wiki['_id'])?>" target="_blank">更多</A></div>
				<?php }?>
			</div>
		</div>
<?php
/* 
foreach($solr_res AS $article) {
	$author = @$article['author'] ?: "";
	$title = @$article['title']['str'] ?: "";
	$article['content'] = preg_replace("#\s#", '', $article['content']);
	$article['content'] = preg_replace("#-{10,}#", '', $article['content']);
	$article_body = tpl_article_substr(strip_tags($article['content']), 300);
	$article_id = $article['articleId'];
	echo <<<HTML
			<div class="Inform">
				<a href="/detail/$region_id/$article_id" target="_blank"><h1>$title</h1></a>
				<div class="display">$article_body</div>
HTML;
		
	if(count($article['images']) > 0) {
		echo <<<HTML
				<div class="youji_tu">
HTML;
		foreach($article['images'] AS $index => $img) {
			if($index > 5) { break; }
			$img = img_proxy($img, $article['url'], 0, 48);
			// width="71" 
			echo <<<HTML
					<h6><img src="$img" height="48" onerror="$(this).css('display', 'none');"/></h6>
HTML;
		}	
		echo <<<HTML
				</div>
HTML;
	}
	$keywords = implode("&nbsp;&nbsp;&nbsp;", $article['keyword']);
	if(!empty($keywords)) { 
		echo <<<HTML
				<div class="labelwz">
					$keywords
				</div>
HTML;
	}
		echo <<<HTML
			</div>
HTML;
}
*/
foreach($documents AS $index => $article) {
	$author = @$article['author'] ?: "";
	$title = @$article['title'] ?: "";
//	$article['content'] = strip_tags($article['content']);
//	$article['content'] = preg_replace("#\s#", '', $article['content']);
//	$article['content'] = preg_replace("#[\-=]{10,}#", '', $article['content']);
	$article_id = strval($article['_id']);
	$fragement_id = strval($article['fragementID']);
	echo '<div class="travel">';
	if (!$index) {
	   echo <<<HTML
			<div class="travel_Title">{$region['name']}游记</div>
HTML;
	}
	echo <<<HTML
			<div class="basic">$author</div>
			<h1><a href="/fragement/$region_id/$fragement_id" target="_blank">$title</a></h1>
HTML;
	/*
	$image_count = count($article['images']);
	$lines = intval($image_count / 3);
	$lines > 3 && $lines = 3;
	if($image_count > 0) {
		foreach($article['images'] AS $index => $img) {
			$current_line = intval($index / 3) + 1;
			if($current_line > $lines) {break;}
			$class = ($index % 3 == 0) ? "wuno" : "";
			$img = img_proxy($img, $article['url'], 205, 110);
			// onerror="$('.arti_{$article_index}_{$current_line}').css('display', 'none');"
			echo <<<HTML
				<h3 class="$class arti_{$article_index}_{$current_line}">
					<img src="{$img}" line="$current_line" width="205" height="110"/>
				</h3>
HTML;
		}
	}
	*/
	$has_image = tpl_echo_article_image($article['pictures']);
	echo <<<HTML
		<br />
HTML;
    echo tpl_article_summary($article['content'], $has_image);
	if(!empty($keywords)) { 
	    $keywords = implode("&nbsp;&nbsp;&nbsp;", $article['keyword']);
		echo <<<HTML
				<div class="labelwz">
					$keywords
				</div>
HTML;
	}
	echo <<<HTML
			</div>
HTML;
}
?>
		
	</div>
	<div id="middle_right">
		<Div class="aside">
			<ul>
				<li><A href="/region/<?=$poi['regionId']?>">首页</A></li>
				<li><A href="/article/<?=$region_id?>">游记</A></li>
				<li class="shouye"><A href="#">景点</A></li>
				<li><A href="#">图片</A></li>
				<li><A href="/map/<?=$poi['regionId']?>">地图</A></li>
			</ul>
		</Div>
<?php if(!empty($subway_nearby)) {?>
		<div class="offside">
			<h1>最近地铁站</h1>
			<div class="description">
<?php 
			foreach($subway_nearby AS $item) {
				if(!$item['dis']) {continue;}
				$subway = $item['obj'];
				$id = strval($item['obj']['_id']);
				$dis = lt_lg_dis_to_real_dis($item['dis'], 'm');
				echo <<<HTML
				<p><a href="/poi/$id">{$subway['name']}</a>&nbsp;($dis)</p>
HTML;
			}

?>
			</div>
		</div>
<?php 
}
?>
		<div class="offside">
			<h1><?=$name?>地图</h1>
			<div class="description">
				<h2><div id="map_area" style="width:190px;height:220px;"></div></h2>
			</div>
		</div>
		<script type="text/javascript" src="http://ditu.google.cn/maps/api/js?sensor=false"></script>
		<script type="text/javascript" src="<?=T?>/javascript/map.js" ></script>
		<script type="text/javascript">
		var mapOptions = {
			id: 'map_area',
			set: {
				rotateControl: false,
				streetViewControl: false,
				scrollwheel: false, 
				panControl: false, 
				zoomControl: false, 
				scaleControl: false, 
				overviewMapControl: false,
				mapTypeControl: false,
				zoom: <?=!empty($poi['zoom']) ? $poi['zoom'] : 13?>
			}
		}
		draw_map(mapOptions);
		var markerOptions = {
			id: 0,
			set:{
				position: <?=!empty($geo) ? json_encode($geo) : 'null'?>,
				address: '<?=$poi['name']?>'
			}
		}
		draw_marker(markerOptions, {display:false});
		</script>
<?php 
if(!empty($attraction_nearby)) {
?>
		<div class="offside">
			<h1>附近景点</h1>
			<div class="description">
<?php 
			foreach($attraction_nearby AS $item) {
				if(!$item['dis']) {continue;}
				$attraction = $item['obj'];
				$id = strval($item['obj']['_id']);
				$dis = lt_lg_dis_to_real_dis($item['dis'], 'm');
				echo <<<HTML
				<p><a href="/poi/$id">{$attraction['name']}</a>&nbsp;($dis)</p>
HTML;
			}

?>
			</div>
		</div>
<?php }?>
<?php if(!empty($poi['people_interred'])) {?>
		<div class="offside">
			<h1>相关历史人物</h1>
			<div class="description">
<?php 
		foreach(explode(",", $poi['people_interred']) AS $key => $value){
		    if($key > 10){break;}
		    $wiki = get_wiki_content($value);
		    $wiki_id = strval($wiki['_id']);
			if($key > 10){break;}
			echo <<<HTML
				<p><a href="/wiki/$region_id/$wiki_id">$value</a></p>
HTML;
		}
?>
			</div>
		</div>
<?php }//people_interred?>
<?php if(!empty($poi['artwork'])) {?>
		<div class="offside">
			<h1>相关艺术品</h1>
			<div class="description">
<?php 
		foreach(explode(",", $poi['artwork']) AS $key => $value){
		    if($key > 10){break;}
		    $wiki = get_wiki_content($value);
	        $wiki_id = strval($wiki['_id']);
			echo <<<HTML
			<p><a href="/wiki/$region_id/$wiki_id">$value</a></p>
HTML;
		}
?>
			</div>
		</div>
<?php }//people_interred?>
	</div>
	<div class="page">
			<a href="<?=($pg > 1 && $total_res_cnt > 1) ? generate_url(array('pg' => $pg - 1)) : '#'?>">上一页</a> 
			<a href="<?=($pg < $total_res_cnt) ? generate_url(array('pg' => $pg + 1)) : '#'?>">下一页</a>
		</div>
</div>
<?php include 'footer.tpl.php';?>

