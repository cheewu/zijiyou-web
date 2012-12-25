<?php include 'header.php'; ?>

<div class="mbread">
	<div class="mbred_page">
		<span><?=$crumbs?></span>
	</div>
</div>
<?php include 'left_category.php';?>

<div id="mail_right">
	<div class="right_center youji_clear">
<?php 
foreach($tags AS $value){
	$wiki = get_wiki_content($value);
	if(!empty($wiki)){
?>
		<div class="right_description article_desc">
			<h2><?=$value?>描述</h2>
			<h4>
				<var><?=utf8_str_cut_off($wiki['content'], 160)?></var>
				<br/>
				<a class="info_preview" href="/search/wiki_preview/<?=$value?>"><span>查看更多</span></a>
			</h4>
		</div>
<?php 
	}
}
?>
		<div class="right_zhishu">
			<div class="right_youji">
				<div class="right_travel youji_clear">
					<h1><?=$name?>游记</h1>
					<div class="youji_tags">
						<ul>
							<li class="label">标签</li>
					<?php
					foreach($tags AS $value){
						echo '<li><a href="#">'.$value.'</a> <a href="'.remove_one_q2_word($article_url, $value).'"><span>x</span></a></li>';
					} 
					?>
						</ul>
						<input id="addtag" type="text" class="youji_searfont"></input><a id="addbutton" href="javascript:void(0)">&nbsp;搜索</a>
						<script type="text/javascript">
						$('#addbutton').click(function(){
							var addtag = $('#addtag').val();
							if(addtag != ''){
<?php 
							$js_url = $article_url;
							if(empty($get['q2'])){
								$js_url = url_append($js_url, 'q2', '', true);
							}else{
								$js_url .= '+';
							}
?>
								window.location = '<?=$js_url?>' + addtag;
							}
						});
						</script>
					</div>
					<?php 
					foreach($article AS $key => $value){
						$url = $value['originUrl'] ?: $value['url'];
						$tmp_content = $highlighting[$value['_id']]['content']['str'];
						//utf8_str_cut_off(trim(strip_tags($value['content'])), 100)
						$tmp_title = !empty($highlighting[$value['_id']]['title']['str']) ? $highlighting[$value['_id']]['title']['str'] : utf8_str_cut_off(trim(strip_tags($value['title'])), 30);   
						echo '
						<div class="travelnotes '.($key == (count($article) - 1 ) ? 'remove' : '').'">
							<h2>'.$tmp_title.'</h2>
							<span>'.(date('Y-m-d', strtotime($value['publishDate']))).'</span>'.
							//<h3>作者：<a href="#">无</a>　 来源：无　 消费浏览：0　 回复：0 </h3>
							'<div class="right_title">'.$tmp_content.'</div>
							<h4>'.$url.'  约'.mb_strlen($value['content'], 'utf-8').'字- - <a class="info_preview" href="/search/article_preview/'.$value['_id'].'">预览</a></h4>
						</div>';//<a href="#">收藏</a>
					}
					?>
				</div>
			</div>
		</div>
		<div class="piece">
			<div class="movie ">
<?php 
		if(empty($get['dr'])){
			echo '<h1>全部时间</h1>';
		}else{
			$tmp_sets = $url_sets;
			unset($tmp_sets['dr']);
			echo '<h1><a href="'.implode_url_set($base_url, $tmp_sets).'">全部时间</a></h1>';
		}
?>
				<ul>
<?php 
		foreach($ci->config->item('month_filter') AS $key => $value){
			if($get['dr'] == $key){
				echo '<li>'.$value.'</li>';
			}else{
				$tmp_sets = $url_sets;
				$tmp_sets['dr'] = $key;
				echo '<li><a href="'.implode_url_set($base_url, $tmp_sets).'">'.$value.'</a></li>';
			}				
		}
?>
				</ul>  
			</div>
			<div class="movie "><?php //除第一个都要有class youji_top?>
				<h1>相关内容</h1>
				<ul>
<?php 
$tas_all = $tags;
$tas_all[] = $name;
			foreach($keyword AS $value){
				$q2 = $tags;
				if(in_array($value, $tas_all)){continue;}
				$q2[] = $value;
				$url_sets['q2'] = implode("+", $q2);
				echo '<li><a href="'.implode_url_set($base_url, $url_sets).'">'.$value.'</a></li>';
				/*
				if(empty($get['q2'])){
					$append_keyword_url = url_append($article_url, 'q2', '', true);
					$append_keyword_url .= $value;
				}else{
					$append_keyword_url = $article_url.'+'.$value;
				}
				echo '<li><a href="'.$append_keyword_url.'">'.$value.'</a></li>';
				*/
			}
?>
				</ul>
			</div>
		</div>
	</div>
</div>

<div class="pages"><?=$multi?></div>
<?php include 'footer.php'; ?>