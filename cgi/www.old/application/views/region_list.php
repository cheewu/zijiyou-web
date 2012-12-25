<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
	<title>Region-管理后台</title>
	<link href="/style/css.css" rel="stylesheet" type="text/css" />
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
					<div id="headerCityButton">
						<a id="jAllCitiesButton" href="" rel="nofollow" class="button">
							<span class="partMiddle">全国</span> 
							<span class="partRight"><!-- &nbsp; --></span>
						</a>
					</div>
				</h2>
			</div>
		</div>
	<div class="lv_title">
	<?php 
			array_unshift($category, 'all');
			$category[] = 'deleted';
			foreach($category AS $key => $value){
				$class = 'category';
				if($key == 0){
					$class .= ' first_block';
				}
				if($get['category'] == $value){
					$class .= ' selected';
				}
				echo '<a href="/region/lists/?category='.$value.'" class="'.$class.'" >'.(isset($category_cn[$value]) ? $category_cn[$value] : $value).'</a>'; 
			}
	?>
		<form id="seasrch_box" action="/region/" method="get" style="float:right;">
			<input type="text" name="q" id="q" value="<?=$get['q']?>"></input>
			<input type="hidden" name="category" id="category" value="<?=$get['category']?>"></input>
			<a onclick="javascript:$('#seasrch_box').submit();" style="cursor:pointer;">搜索</a>
		</form>
	</div>
	<div class="pagin pagin-m fr">
		<span class="text"><?=$pg;?>/<?=$total_pg;?></span>
		<a class="<?=$prev?>" href="<?=$prev_url;?>">上一页<b></b></a> 
		<a class="<?=$next?>" href="<?=$next_url;?>">下一页<b></b></a>
	</div>
	<div class="travel_table">
		<table width="1001px" border="0" align="center" cellpadding="0" cellspacing="0" style="border-left: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC;table-layout:fixed;">
			<tr class="lv_color">
				<td width="9%" bgcolor="#FF0000">地域</td>
				<td width="9%">名称</td>
				<td width="7%">类别</td>
				<td width="5%">poi数</td>
				<td width="8%">英文名</td>
				<td width="13%">关键词</td>
				<td width="15%">描述</td>
				<td width="15%">经纬度</td>
				<td width="5%">时区</td>
				<td width="3%">热门</td>
				<td width="3%">网站</td>
				<td width="3%">博客</td>
				<td width="3%">微博</td> 
				<td width="3%">操作</td>
			</tr>
<?php 
	foreach($result AS $key => $value){
		echo '<tr>';
			echo "<td>{$value['area']}</td>";
			echo "<td>{$value['name']}</td>";
			echo "<td>{$value['category']}</td>";
			if($value['poi_cnt']){
				echo "<td><a href='/poi/?q={$value['name']}' target='_blank'>{$value['poi_cnt']}</a></td>";
			}else{
				echo "<td>{$value['poi_cnt']}</td>";
			}
			echo "<td>{$value['englishName']}</td>";
			echo "<td>{$value['keyword']}</td>";
			echo "<td>{$value['desc']}</td>";
			echo "<td>".(isset($value['center']) ? (substr(floatval($value['center'][0])."", 0, 13)).",<br/>".(substr(floatval($value['center'][1])."", 0, 13)) : "&nbsp;")."</td>";
			echo "<td>{$value['timezone']}</td>";
			echo "<td>{$value['is_important']}</td>";//istravel
			echo "<td><a href='".($value['website'] ?: $value['url'])."'>".($value['website'] ? "网站" : "来源")."</a></td>";
			echo "<td><a href='".($value['blog'] ?: "#")."'>".($value['blog'] ? "博客" : "")."</a></td>";
			echo "<td><a href='".($value['weibo'] ?: "#")."'>".($value['weibo'] ? "微博" : "")."</a></td>";
			echo "<td style='text-align:center;'><a href='/region/item/{$value['_id']}?pg={$pg}".(isset($get['q']) ? "&q={$get['q']}" : "")."'>操作</a></td>";
		echo '</tr>';
	}
?>
		</table>
	</div>
</div>

<div class="travel_bottomnavbg"></div>
</div>
</body>
</html>
