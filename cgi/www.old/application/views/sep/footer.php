	<div class="clear"></div>
<!--end of box-->
</div> 
<!--主要内容结尾-->

	<div class="footer">
		<div class="footer_content">@2011 lvyou <a href="#">使用前必读</a> <a href="#">旅游用户协议</a> <a href="#">联系我们</a></div>
	</div>
	<div class="map_pic_area"></div>
<!--js-->
<script type="text/javascript">
$(document).ready(function() {
	
	$('.info_preview').fancybox({
		'width'				: 800,
		'height'			: '75%',
		'autoScale'			: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'type'				: 'iframe'
	});
	
	$("#query").autocomplete("/search/query_relative/", {
		minchars: 1,
		max: 9,
		delay: 0,
		mustmatch: true,
		matchcontains: false,
		scrollheight: 220,
		selectFirst: false,
		cacheLength:1,
//		width: 260,
//		scroll: true,
		formatitem: function(data, i, total) {
			if(data[1]=="a"){
				return '<strong>'+data[0]+'</strong>';
			}
			return data[0];
		}
	});
	$('a.pre_view').click(function(){
		var data = {
			id: $(this).attr('id'),
			cate: $(this).attr('cate'),	
		};
		$.ajax({
			'type': "GET",
			'url': "/search/article_note/",
			'dataType': 'html',
			'data': data,
			'success': function(res){
				alert(res);
			}
		});
	});
});
</script>
<!-- solr debug -->
<?php
	$get = $ci->input->get();
	$cookie = $ci->users_handle->cookie;
	if( ( (!empty($get['solr_debug']) && $get['solr_debug'] == 1) || $cookie['solr_debug'] == 1 ) || ( (!empty($get['mongo_debug']) && $get['mongo_debug'] == 1) || $cookie['mongo_debug'] == 1) ) {
?>
<div id="debug">
	<?php
	if( (!empty($get['solr_debug']) && $get['solr_debug'] == 1) || $cookie['solr_debug'] == 1) {
	?>
	<table>
		<caption>Solr Debug Info</caption>
		<?php
		if( !empty($_SGLOBAL['debug_info']['solr']) ) {
			foreach( $_SGLOBAL['debug_info']['solr'] as $val ) { 
			?>
			<tr>
				<td width="12%" class='td_center'>请求后台URL</td>
				<td class='td_content'>
					<a href="<?=h($val['request_url'])?>" target=_blank><?=h(urldecode($val['request_url']));?></a>&nbsp;&nbsp;
					<br/><br/>请求耗时：<?=substr($val['time_cost'],0,6)?>(s)
					<br/><br/>
					原始URL：<?=h($val['request_url']);?>
				</td>
			</tr>
			<?php
			} 
		}
		?>
	</table>
	<?php
	} 
	?>
	<!-- solr debug -->
	<!-- mongo debug -->
	<?php
	if( (!empty($get['mongo_debug']) && $get['mongo_debug'] == 1) || $cookie['mongo_debug'] == 1) {
	?>
	<table>
		<caption>Mongo Debug Info</caption>
		<?php
		if( !empty($_SGLOBAL['debug_info']['mongo']) ) {
			foreach( $_SGLOBAL['debug_info']['mongo'] as $key => $val ) { 
			?>
			<tr>
				<td width="12%" class='td_center'>后台Mongo查询</td>
				<td class='td_content'>
					<a class="debug_mongo_fancy" href="<?='#'.$key.'_mongo_query'?>" target=_blank>查看请求</a>&nbsp;&nbsp;
					<br/><br/>请求耗时：<?=substr($val['time_cost'],0,6)?>(s)
					<br/><br/>
					
				</td>
			</tr>
			<div style="display:none;"><div id="<?=$key.'_mongo_query'?>" style="width:500px;height:75%;"><pre class='mongo_var'><code><?=var_dump($val['mongo_query'])?></code></pre></div></div>
			<?php
			} 
		}
		?>
	</table>
	<script type="text/javascript">
	$('.debug_mongo_fancy').fancybox({
		'titlePosition'		: 'inside',
		'transitionIn'		: 'none',
		'transitionOut'		: 'none'
	});
	</script>
	<?php
	} 
	?>
</div>
<!-- mongo debug -->
<?php }?>
</body>
</html>