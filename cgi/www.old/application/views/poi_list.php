<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
<title>POI管理后台</title>
<link href="/style/css.css" rel="stylesheet" type="text/css" />
<link href="/style/gaopeng.css" rel="stylesheet" type="text/css" />
<link href="/style/search.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.min.js"></script>
</head>
<body>
<div id="travel_background">
<div id="box">
<div id="lv_header">
<div class="lv_top">
<h1>中国</h1>
<h2>
<div id="headerCityButton"><a id="jAllCitiesButton" href=""
	rel="nofollow" class="button"><span class="partMiddle">全国</span> <span
	class="partRight"><!-- &nbsp; --></span></a></div>
</h2>
</div>
</div>
<div class="lv_title">
<?php 
	array_unshift($category, 'all');
	$category[] = 'none';
	$category[] = 'deleted';
	foreach($category AS $key => $value){
		$class = 'category';
		if($key == 0){
			$class .= ' first_block';
		}
		if($get['category'] == $value){
			$class .= ' selected';
		}
		echo '<a href="/poi/lists/?category='.$value.'&q='.$get['q'].'" class="'.$class.'" >'.$value.'</a>'; 
	}
?>
	<form id="seasrch_box" action="/poi/" method="get" style="float:right;">
	<input type="text" name="q" id="q"></input>
	<a onclick="javascript:$('#seasrch_box').submit();" style="cursor:pointer;">搜索</a>
	</form>
</div>

<div class="pagin pagin-m fr">
<span class="text"><?=$pg;?>/<?=$total_pg;?></span>
<a class="<?=$prev?>" href="<?=$prev_url;?>"> 上一页<b></b>
</a> <a class="<?=$next?>" href="<?=$next_url;?>">
下一页<b></b> </a></div>
<div class="travel_table">
<table width="1001px" border="0" align="center"
	cellpadding="0" cellspacing="0"
	style="border-left: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC;">
	<tr class="lv_color">
		<td width="7%" bgcolor="#FF0000">区域</td>
		<td width="7%">名称</td>
		<td width="7%">英文称</td>
		<td width="9%">关键词</td>
		<td width="10%">经纬度</td>
		<td width="10%">门票</td>
		<td width="5%">电话</td>
		<td width="15%">描述</td>
		<td width="10%">营业时间</td>
		<td width="5%">地址</td>
		<td width="3%">图片</td>
		<td width="3%">网站</td>
		<td width="3%">微博</td>
		<td width="3%">博客</td>
		<td width="3%">操作</td>
	</tr>
	<?php 
		if(count($result) > 0) {
			foreach ($result as $key => $value) {

	?>
	<tr>
		<td bgcolor="#FF0000"><?=$value['area']?></td>
		<td><?=$value['name']?></td>
		<td><?=$value['englishName']?></td>
		<td><?=$value['keyword']?></td>
		<td class="att"><?=isset($value['center']) ? (substr(floatval($value['center'][0])."", 0, 13)).",<br/>".(substr(floatval($value['center'][1])."", 0, 13)) : "&nbsp;" //经纬度展示时取小数点后9位 ?></td> 
		<td><?=utf8_str_cut_off($value['ticket'], 20) ?: "&nbsp;"?></td>
		<td><?=utf8_str_cut_off($value['telNum'], 20)?></td>
		<td><?=utf8_str_cut_off(strip_tags($value['desc']), 20)?></td>
		<td><?=utf8_str_cut_off($value['openTime'], 20)?></td>
		<td><?=utf8_str_cut_off($value['address'], 20) ?: "&nbsp;"?></td>
		<td><?=$value['images'] ?: "&nbsp;"?></td>
		<td><a href="<?=$value['website'] ?: $value['url'] ?>"><?=$value['website'] ? "网站" : "来源" ?></a></td>
		<td><a href="#"><?=$value['weibo'] ? "微博" : ""?></a></td>
		<td><a href="#"><?=$value['blog'] ? "博客" : ""?></a></td>
		<td><a href="/poi/item/<?=$value['_id']?>?pg=<?=$pg?><?=isset($get['q']) ? "&q={$get['q']}" : ""?>">编辑</a></td>
	</tr>
	<?php 
			}
		}
	?>
</table>
</div>
</div>

<div class="travel_bottomnavbg"></div>
</div>

<script type="text/javascript">
$(document).ready(function() {
	$('#cities_popup dl').hover(function() {
		$(this).css({background: '#cfeef6'});
	}, function() {
		$(this).css({background: '#fff'});
	});
	// handle click event on close button
	$('#close_cities_popup').click(function(event) {
		$('#cities_popup').fadeOut('fast');
		event.preventDefault();
	});

	$('#jAllCitiesButton').click(function(event) {
		$('#cities_popup').fadeIn('fast');
			event.preventDefault();
			event.stopPropagation();
		}

	);

	$('#cities_popup').click(
		function(event) {
			event.stopPropagation();
		}	
	);

	$(document).click(
			function() {
				$('#cities_popup').fadeOut('fast');	
			}
	)

	$('#cities_popup dd a').each(
			function () {
				text = $(this).text();
				if(text == '全国') {
					$(this).attr('href', "/poi/lists");
				} else {
					$(this).attr('href', "/poi/lists/"+text);
				}
			}
			);
	 
});


function toCreate() {
	document.location="/poi/create/<?php echo urlencode($city)?>"
}
</script>
</body>
</html>
