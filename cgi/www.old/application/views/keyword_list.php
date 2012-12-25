<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
	<title><?=$title?></title>
	<link href="/style/keywordcss.css" rel="stylesheet" type="text/css" />
	<link href="/style/gaopeng.css" rel="stylesheet" type="text/css" />
	<link href="/style/search.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="/js/jquery.min.js"></script>
	<!--fancybox-->
	<script src="/fancybox/jquery.mousewheel-3.0.4.pack.js" type="text/javascript"></script>
	<script type="text/javascript" src="/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
	<link rel="stylesheet" type="text/css" href="/fancybox/jquery.fancybox-1.3.4.css" media="screen"/>
	<!--fancybox-->
</head>
<body>
<div id="travel_background">
	<div id="box">
		<div class="lv_top">
			<h1>中国</h1>
			<h2>
				<div id="headerCityButton">
					<span class="partMiddle">全国</span> 
					<span class="partRight"></span>
				</div>
			</h2>
		</div>	
		<div class="lv_title">
		<?php 
			foreach($category AS $key => $value){
				$class = 'category';
				if($key == 0){
					$class .= ' first_block';
				}
				if($selected_category == $value){
					$class .= ' selected';
				}
				echo '<a href="/keyword/lists/'.$value.'" class="'.$class.'" >'.$value.'</a>'; 
			}
		?>
			<?php 
				if(!empty($type)){
					if($type == 'group'){
						echo '<a href="/keyword/lists/'.$selected_category.'/'.$selected_char.'?'.$append_url.'&pg='.$pg.'" class="category" >返回</a>';
					}
					if($type == 'del'){
						echo '<a href="/keyword/lists/" class="category" >返回</a>';
					}
				}else{
					echo '<a href="/keyword/modify/" class="keyword_list_modi category" >添加</a>';
					echo '<a href="/keyword/lists/'.$selected_category.'/'.$selected_char.'?'.$append_url.'&pg='.$pg.'&type=group" class="category" >批量删除</a>';
					echo '<a href="/keyword/lists/del" class="category" >查看删除</a>';
					
				}
			?>
		</div>
		<div class="pagin pagin-m fr">
			<span class="text"><?=$pg.'/'.$total_pg ?></span>
			<a class="<?=$prev ?>" href="<?=$prev_url ?>">上一页<b></b></a> 
			<a class="<?=$next ?>" href="<?=$next_url ?>">下一页<b></b></a>
		</div>
		<div class="travel_table search_result">
			<div class="char_category">
				<ul class="char_title">
				<?php 
					$char_arr = array();
					for($i = 0; $i < 26; $i++){
						$char_arr[] = chr($i+65);
					}
					$char_arr[] = 'all';
					foreach($char_arr AS $key => $value){
						$class = 'single_char';
						if($key == 0){
							$class .= ' first_block';
						}
						if($selected_char == $value){
							$class .= ' selected';
						}	
						echo "<a href='/keyword/lists/{$selected_category}/{$value}?{$append_url}'><ol class=\"{$class}\">{$value}</ol></a>";
					}
					
				?>
				</ul>
				
				<?php 
			if($selected_lan != 'mix'){
				echo '<ul class="char_title">';
				
					$char_arr = array();
					for($i = 1; $i <= 10; $i++){
						$char_arr[] = $i;
					}
					$char_arr[] = 'gt10';
					foreach($char_arr AS $key => $value){
						$class = 'single_char';
						if($key == 0){
							$class .= ' first_block';
						}
						if($selected_cnt == $value){
							$class .= ' selected';
						}
						$append_url_cnt = !empty($selected_lan)	? 'lan='.$selected_lan : '';
						echo "<a href='/keyword/lists/{$selected_category}/{$selected_char}?{$append_url_cnt}&len={$value}'><ol class=\"{$class}\">{$value}</ol></a>";
					}
				echo '</ul>';	
			}
				?>
				</ul>
				<ul class="char_title">
				<?php 
					$char_arr = array();
					foreach(array('cn', 'en', 'mix', 'all') AS $key => $value){
						$class = 'single_char';
						if($key == 0){
							$class .= ' first_block';
						}
						if($selected_lan == $value){
							$class .= ' selected';
						}	
						$append_url_lan = !empty($selected_cnt)	? 'len='.$selected_cnt : '';
						echo "<a href='/keyword/lists/{$selected_category}/{$selected_char}?{$append_url_lan}&lan={$value}'><ol class=\"{$class}\">{$value}</ol></a>";
					}
				?>
				</ul>
				<ul class="keyword_body">
				<?php 
					foreach($result AS $value){
						if($type == 'group'){
							echo "<div class='keyword_box' id=\"{$value['_id']}\"><div class=\"keyword_list\"   ><div class=\"keyword_content_shell\"><span class=\"keyword_content\">{$value['keyword']}</span></div></div>";
							echo "<img class='fancy_close_button' src='/fancybox/fancy_close.png' data='{$value['_id']}'></img>
							<div class='clear'></div>
							</div>";
						}elseif($type == 'del'){
							echo "<div class='keyword_box' id=\"{$value['_id']}\"><div class=\"keyword_list\"   ><div class=\"keyword_content_shell\"><span class=\"keyword_content\">{$value['keyword']}</span></div></div>";
							echo "</div>";
						}else{
							echo "<a href=\"/keyword/modify/{$value['_id']}\" class=\"keyword_list keyword_list_modi\"  id=\"{$value['_id']}\" ><div class=\"keyword_content_shell\"><span class=\"keyword_content\">{$value['keyword']}</span></div></a>";
						}

					}
				?>
				<div class="clear"></div>
				</ul>
			</div>
			
			
		</div>
	</div>
	<div class="travel_bottomnavbg"></div>
</div>
<script type="text/javascript">
$(document).ready(function() {

		$('.fancy_close_button').click(function(){
			id = $(this).attr('data');
				$.ajax({
				'type': "GET",
				'url': "/keyword/remove_ajax/",
				'dataType': 'html',
				'data': 'id=' + id,
				'success': function(sig){
					if(sig == 'success'){
						$('#'+id).fadeOut();
					}
				}
			});
		});
		

		$(".keyword_list_modi").fancybox({
			'overlayOpacity': 0.6,
			'width': 300,
			'height': 130,
			'autoDimensions': true,
			'changeSpeed': 10,
			'type': 'iframe',
			'resize': true,
			'padding': 5,
			'margin': 5,

			'hideOnContentClick': false,
			
			'autoScale'			: false,
			'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'onClosed': function(){	
			}
		});
})	
</script>


</body>