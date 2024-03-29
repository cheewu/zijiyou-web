<?php include 'header.tpl.php';?>
<div id="middle">
	<div class="classify">
		<?=crumbs($region_id, "", ($keyword == $region['name']) ? array() : array($keyword))?>
	</div>
	<div id="middle_left">
<?php 
foreach($documents AS $article_index => $article) {
	$author = @$article['author'] ?: "";
	$title = @$article['title'] ?: "";
	empty($article['content']) && $article['content'] = "";
	//$article['content'] = strip_tags($article['content']);
	//$article['content'] = preg_replace("#\s#", '', $article['content']);
	//$article['content'] = preg_replace("#[\-=]{10,}#", '', $article['content']);
	//$contents = utf8_substr_ifneeed($article['content'], 300, false, '...');
	$r_id = strval($region['_id']);
	$a_id = strval($article['_id']);
	echo <<<HTML
		<div class="travel">
			<div class="basic">$author</div>
			<h1><a href="/detail/$r_id/$a_id">$title</a></h1>
HTML;
    $has_image = tpl_echo_article_image($article['pictures']);
/*
	$image_count = count($article['images']);
	$lines = intval($image_count / 3);
	$lines > 3 && $lines = 3;
	if($lines > 0) {
		foreach($article['images'] AS $index => $img) {
			$current_line = intval($index / 3) + 1;
			if($current_line > $lines) {break;}
			$class = ($index % 3 == 0) ? "wuno" : "";
			$img = get_article_pic_by_index(strval($article['_id']), $index, 205, 110);
			echo <<<HTML
			<h3 class="$class arti_{$article_index}_{$current_line}">
				<img src="{$img}" line="$current_line" width="205" height="110" onerror="$('.arti_{$article_index}_{$current_line}').css('display', 'none');"/>
			</h3>
HTML;
		}
	}
*/
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
		<div class="page">
			<a href="<?=($pg > 1 && $total_res_cnt > 1) ? generate_url(array('pg' => $pg - 1)) : '#'?>">上一页</a> 
			<a href="<?=($pg < $total_res_cnt) ? generate_url(array('pg' => $pg + 1)) : '#'?>">下一页</a>
		</div>
	</div>
	<div id="middle_right">
		<Div class="aside">
			<ul>
				<li><A href="/region/<?=$region_id?>">首页</A></li>
				<li class="shouye"><A href="#">游记</A></li>
				<li><A href="/attraction/<?=$region_id?>">景点</A></li>
				<li><A href="#">图片</A></li>
				<li><A href="/map/<?=$poi['regionId']?>">地图</A></li>
			</ul>
		</Div>
<?php 
if (!empty($correlation_words)) {
?>
	<div class="aside aside_text">
		<ul>
<?php 
    $count = 0;
    foreach ($correlation_words AS $name => $score) {
        if (++$count > 20) { break; }
        echo <<<HTML
			<li><A href="/article/$region_id/?keyword=$name">$name</A></li>
HTML;
    }
?> 
    	</ul>
	</div>
    
<?php }?>
<?php 
if(0) {
?>
		<div class="youji_ss"><input name="wenben" type="text" class="Search_label" value="搜索标签" /><input name="ss" class="anniu" type="image" src="<?=T?>/images/ss.jpg" /></div>
		<Div class="aside aside_text">
			<ul>
				<li><A href="#">烤鸭</A></li>
				<li><A href="#">涮羊肉</A></li>
				<li><A href="#">满汉全席</A></li>
				<li><A href="#">爆肚豆汁</A></li>
			</ul>
		</Div>

		<Div class="aside aside_text">
			<ul>
				<li><A href="#">游记 </A></li>
				<li><A href="#">购物</A></li>
				<li><A href="#">美食</A></li>
				<li><A href="#">交通</A></li>
				<li><A href="#">住宿</A></li>
			</ul>
		</Div>

		<Div class="aside aside_text">
			<ul>
				<li><A href="#">最近三个月</A></li>
				<li><A href="#">最近半年</A></li>
				<li><A href="#">最近一年</A></li>
				<li><A href="#">一年之前</A></li>
			</ul>
		</Div>
<?php }?>
	</div>
</div>
<?php include 'footer.tpl.php';?>