<?php
	empty($left_set['pre_name']) && $left_set['pre_name'] = $region['name'];
	empty($left_set['link_name']) && $left_set['link_name'] = $region['_id'];
?>
<div id="main_left">
	<div class="left_whole">
		<div class="borderimg">
			<img src="/images/sep/tour_01.jpg" />
		</div>
		<div class="left_content">
			<ul>
				<?php $name_encode = rawurlencode($left_set['link_name']);?>
				<li <?=empty($category) ? 'class="left_title"' : ''?>><a href="/search/region/<?=$name_encode?>"><?=$left_set['pre_name']?>首页</a></li>
				<li <?=$category == 'article' ? 'class="left_title"' : ''?>><a href="/search/article/<?=$name_encode?>"><?=$left_set['pre_name']?>游记</a></li>
				<li <?=$category == 'attraction' ? 'class="left_title"' : ''?>><a href="/search/attraction/<?=$name_encode?>"><?=$left_set['pre_name']?>景点</a></li>
				<li <?=$category == 'traffic' ? 'class="left_title"' : ''?>><a href="/search/traffic/<?=$name_encode?>"><?=$left_set['pre_name']?>交通</a></li>
<!--				<li <?=$category == 'stay' ? 'class="left_title"' : ''?>><a href="/search/stay/<?=$name_encode?>"><?=$left_set['pre_name']?>住宿</a></li>-->
				<li <?=$category == 'shop' ? 'class="left_title"' : ''?>><a href="/search/shop/<?=$name_encode?>"><?=$left_set['pre_name']?>购物</a></li>
				<li <?=$category == 'food' ? 'class="left_title"' : ''?>><a href="/search/food/<?=$name_encode?>"><?=$left_set['pre_name']?>美食</a></li>
<!--				<li><a href="#">图片</a></li>-->
			</ul>
		</div>
		<div class="borderimg">
			<img src="/images/sep/tour_02.jpg" />
		</div>
	</div>
</div>