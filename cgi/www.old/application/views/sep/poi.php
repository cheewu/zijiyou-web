<?php include 'header.php'; ?>

<div class="mbread">
	<div class="mbred_page">
		<span><?=$crumbs?></span>
	</div>
</div>

<?php include 'left_category.php';?>

<div id="mail_right">
	<div class="right_middle">
		<div class="right_description poi_kuan">
			<h1><?=$name?></h1>
			<?php 
			$detail['desc'] = trim(strip_tags($detail['desc']));
			$desc = $detail['desc'];
			$detail['desc'] = utf8_str_cut_off($detail['desc'], 150) ?: utf8_str_cut_off($wiki['content'], 150).'<br /><a class="info_preview" href="/search/wiki_preview/'.$name.'"><span>查看更多</span></a>';
			?>
			<h4>
				<?=!empty($detail['telNum']) ? $detail['telNum'].'</br>' : '' ?>
				<?=$detail['website'] ?: '' ?>
			</h4>
			
			<h4>
				<div style="display:none;"><div id="desc_box" style="width:800px;"><?=nl2br($desc)?></div></div>
				<?=!empty($detail['desc']) ? $detail['desc'].'</br>' : '' ?>
				<?php 
				if(!empty($desc)){echo '<a id="desc_trigger" href="#desc_box">查看更多</a>';}
				?>
				
			</h4>
			<?php 
			if($detail['category'] == 'subway' && !empty($subinfo['lines'])){
				echo '
				<h4>地铁线路: '.implode(", ", tpl_add_line_wiki_link($subinfo['lines'])).'</h4>
				';
			}elseif(!empty($subinfo['stops'])){
				echo '
				<h4>地铁站：';
				foreach($subinfo['stops'] AS $value){
					$lines = implode(", ", tpl_add_line_wiki_link($value['stopline']));
					!empty($lines) && $lines = '--'.$lines;
					echo '
						<a href="/search/poi/'.$value['_id'].'">'.$value['name'].'</a>'.$lines.'（'.$value['dis'].'）
					';
				}
				echo '
				</h4>';
			}
			?>
		</div>  
		<div class="right_img poi_maps">
		<?php //地图大小用 指定?>
			<div class="right_map" id="map_area" style="width:300px;height:220px;"></div>
		</div>
    </div>
	<div class="right_center">
		<div class="right_zhishu right_tieshi">
			<div class="right_youji">
				<div class="right_travel youji_clear">
					<h1><?=$name?>游记</h1>
					<div class="renwu">
						<a href="/search/poi/<?=$detail['_id']?>">全部间时</a>　 
						<a href="/search/poi/<?=$detail['_id']?>/?dr=1">最近一个月</a>　 
						<a href="/search/poi/<?=$detail['_id']?>/?dr=3">最近三个月</a> 　 
						<a href="/search/poi/<?=$detail['_id']?>/?dr=6">最近六个月</a>
					</div>
					<?php 
					$count = count($article);
						foreach($article AS $key => $value){
							$tmp_content = $highlighting[$value['_id']]['content']['str'];
							$tmp_title = !empty($highlighting[$value['_id']]['title']['str']) ? $highlighting[$value['_id']]['title']['str'] : utf8_str_cut_off(trim(strip_tags($value['title'])), 30);
							$url = $value['originUrl'] ?: $value['url'];
							echo '
								<div class="travelnotes '.($count == $key + 1 ? 'remove' : '').'">
									<h2>'.$tmp_title.'</h2>
									<div class="right_title">'.$tmp_content.'</div>
									<h3>时间： '.date("Y-m-d", strtotime($value['publishDate'])).'  <a style="color:#A0A0A0;" href="'.$url.'">'.utf8_str_cut_off($url, 60).'</a>   <a class="info_preview" href="/search/article_preview/'.$value['_id'].'">预览</a></h3>
								</div>';
						}
					?>
				</div>
			</div>
		</div>
<!--right-sort 可用模板替换-->
		<div class="piece shijian">
<?php if(!empty($place_nearby)){ ?>
			<div class="right_difang">
				<div class="right_travel youji_clear">
					<h1>相关地点</h1>
				</div>
<?php 
		foreach($place_nearby AS $value){
			$wiki = get_wiki_content($value['name']);
			echo '
				<div class="difang_nr">
					<h1><a href="/search/poi/'.$value['_id'].'">'.$value['name'].'</a> <!--东-->距离 '.$value['dis'].' </h1>
					<h2>'.utf8_str_cut_off($wiki['content'], 20).'</h2>
				</div>
			
			';
			
		}
?>
<?php /* ?>
				<div class="difang_nr">
					<h1><a href="#">树正沟</a> 东 1.3 公里 </h1>
					<h2>四川省阿坝藏族羌族自治州, 九寨沟</h2>
					<h3>29 篇评论 - 景区 </h3>
				</div>
<?php */ ?>
			</div>
<?php }?>
<?php if(!empty($detail['film'])) {?>
			<div class="movie poi_nr">
				<h1>相关电影</h1>
<?php 
			foreach(explode(",", $detail['film']) AS $key => $value){
				if($key > 10){break;}
				echo '
				<ul>
					<li><a href="#">'.trim($value).'</a></li>
				</ul>';
			}
?>
			</div>
<?php }//film?>
<?php if(!empty($detail['people_interred'])) {?>
			<div class="movie poi_nr">
				<h1>相关历史人物</h1>
<?php 
			foreach(explode(",", $detail['people_interred']) AS $key => $value){
				if($key > 10){break;}
				echo '
				<ul>
					<li><a href="#">'.trim($value).'</a></li>
				</ul>';
			}
?>
			</div>
<?php }//people_interred?>
<?php if(!empty($detail['artwork'])) {?>
			<div class="movie poi_nr">
				<h1>相关艺术品</h1>
<?php 
			foreach(explode(",", $detail['artwork']) AS $key => $value){
				if($key > 10){break;}
				echo '
				<ul>
					<li><a href="#">'.trim($value).'</a></li>
				</ul>';
			}
?>
			</div>
<?php }//people_interred?>
		</div>
<!--end！！right-sort 可用模板替换-->
	</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$('#desc_trigger').fancybox({
		'titlePosition'		: 'inside',
		'transitionIn'		: 'none',
		'transitionOut'		: 'none'
	});
	var mapOptions = {
			id: 'map_area',
			set: {zoom: 14},
		}
		draw_map(mapOptions);

		var markerOptions = {
			id: 0,
			set:{
				title: '<?=$name?>',
				address: '<?=$name?>',
				position: <?=!empty($geo) ? json_encode($geo) : 'null'?>,
			},
		}
		draw_marker(markerOptions);
});
</script>

<div class="pages"><?=$multi?></div>

<?php include 'footer.php'; ?>
