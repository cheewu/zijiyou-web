<?php

function tpl_add_id($detail) {
	echo <<<HTML
	<input name="_id" value="{$detail['_id']}" type="hidden"/>
HTML;
}

function tpl_add_botton() {
	$ci = &get_instance();
	$collection = strtolower($ci->query['collection']);
	if($collection == 'wikipedia') {return;}
	$append = isset($ci->query['regionId']) ? "?regionId=".$ci->query['regionId'] : "";
	echo <<<HTML
	<div class="add_botton">
		<a href="/$collection/add$append" target="_blank">
			<input value="添加" type="button" />
		</a>
	</div>
	<hr/>
HTML;
}

//input
function tpl_input_text($input_arr, $detail) {
	$ci = &get_instance();
	if(isset($ci->query['regionId'])) {
    	$region = $ci->mongo_db->Region_fetch_by__id($ci->query['regionId']);
    	$detail['regionId'] = $region['_id'];
    	$detail['area']     = $region['name'];
	}
//    if(isset($detail['regionId'])) {
//    	$region = $ci->mongo_db->Region_fetch_by__id($detail['regionId']);
//    	$detail['area']     = $region['name'];
//	}
	if(empty($input_arr)) {return '';}
	empty($detail['regionId']) && $detail['regionId'] = "";
	echo <<<HTML
	<input id="regionId" name="regionId" type="hidden" value="{$detail['regionId']}"/>
HTML;
	foreach($input_arr AS $key => $name) {
		$disable = (!empty($ci->conf['disable']) && in_array($key, $ci->conf['disable'])) ? 'disabled="disabled"' : '';
		echo <<<HTML
	<div class="input_text">
		<span class="_input_text_name">$name:</span>
HTML;
        if($ci->collection == 'POI' && $key == 'area') {
        echo <<<HTML
		<input id="input_search" type="text" value="$detail[$key]" style="width:30%;"/>
		<select id="input_select" style="width:35%;">
			<option value="{$detail['regionId']}" selected="selected">{$detail['area']}</option>
		</select>
HTML;
        } else {
            echo <<<HTML
		<input type="text" $disable name="$key" value="$detail[$key]"/>
HTML;
        }
		echo <<<HTML
	</div>
HTML;
	    if($ci->collection == 'POI' && $key == 'area') {
            echo <<<HTML
<script type="text/javascript">
	$('#input_search').bind(($.browser.opera ? "keypress" : "keyup"), function(event) {
		var key = $.browser.mozilla ? event.charCode : event.keyCode;
		do {
    		if($.browser.mozilla && key == 0) { break; }
    		if(key >= 47 || key <= 91) { break; }
    		if(key == 8 || key == 32) { break;}
    		return;
    	} while(0);
    	
    	setTimeout(function(){
	    	$.ajax({
            	type: "GET",
            	url: "/ajax/regionSearch/" + $('#input_search').val(),
            	dataType: 'json',
            	success: function(sig) {
        			if(sig.id) {
        				$('#input_select').html(sig.html);
        				$('#regionId').val(sig.id);
          			}
        		}
    		});
	    }, 500);
	});
	$('#input_select').change(function(){
		$('#regionId').val(this.value);
	});
</script>
HTML;
		}
	}
	echo "<hr/>";
}

//select
function tpl_select($select_arr, $detail, $sub_select_arr) {
	if(empty($select_arr)) {return '';}
	foreach($select_arr AS $key => $select) {
		echo <<<HTML
		<div class="select">
			<span>$select:</span>
			<select name="$key">
HTML;
		foreach($sub_select_arr[$key] AS $option) {
			$is_selected = $detail[$key] == $option ? 'selected="selected"' : ''; 
			echo <<<HTML
			<option value="$option" $is_selected>$option</option>
HTML;
		}
		echo "
			</select>
		</div>
		";
	}
	echo "<hr/>";
}

//textarea
function tpl_textarea($textarea_arr, $detail, $is_json = true) {
	$ci = &get_instance();
	if(empty($textarea_arr)) {return '';}
	foreach($textarea_arr AS $key => $textarea) {
		//!isset($detail[$key]) && $detail[$key] = '';
		//pr($ci->conf['json']);
		$value = !empty($detail[$key]) ? $detail[$key] : '';
		if(!empty($ci->conf['json']) && array_key_exists($key, $ci->conf['json'])) {
			!empty($value) && $value = json_unicode_to_utf8(json_encode($value));
			$textarea .= '(json)';
		}
		if(!empty($ci->conf['xml']) && array_key_exists($key, $ci->conf['xml'])) {
			foreach($ci->conf[$key] AS $node_name) {
				empty($value[$node_name]) && $value[$node_name] = '';
			}
			ksort($value);
			$value = _array_to_xml($value);
			$textarea .= '(xml)';
		}
		echo <<<HTML
		<div class="textarea_wrapper">
			<div class="textarea">
				<span>$textarea:</span>
				<textarea name="$key">$value</textarea>
			</div>
		</div>
HTML;
	}
	echo "<hr/>";
}

//map
function tpl_map($center, $detail){
	$position = !empty($center) ? $center[0].', '.$center[1] : '0, 0';
	$detail['map_zoom'] = isset($detail['map_zoom']) ? $detail['map_zoom'] : 10;
	echo <<<HTML
<script type="text/javascript" src="http://ditu.google.cn/maps/api/js?sensor=false"></script>
<div class="map_area">
	<span>地址：</span>
	<input id="address" name="address" value="{$detail['address']}" type="text"/>
	<input onclick="javascript:search()" type="button" value="搜索"/>
</div>
<div id="google_map"></div>
<input id="lat" name="lat" type="hidden" value="$center[0]" />
<input id="lng" name="lng" type="hidden" value="$center[1]" />
<input id="map_zoom" name="map_zoom" type="hidden" value="{$detail['map_zoom']}" />
<script type="text/javascript">
	var map;
	var geocoder = new google.maps.Geocoder();
	var marker; //记录marker的变量
	var position = new google.maps.LatLng($position)
	var myOptions = {
		zoom: {$detail['map_zoom']},
		panControl: true,
		zoomControl: true,
		scaleControl: true,
		center: position,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	}
</script>
<script type="text/javascript" src="/js/tpl_map.js"></script>
<hr/>
HTML;
}

//upload
function tpl_upload_img($_id, $collection, $detail) {
	$img_url = !empty($detail['img_path']) ? rawurlencode('/tmp/'.basename($detail['img_path'])) : '';
	echo <<<HTML
<input id="img_path" name="img_path" value="{$detail['img_path']}" type="hidden" />
<iframe id="pic_upload_iframe" name="content_frame" marginwidth=0 marginheight=0 src="/post/pic_upload_iframe?_id=$_id&collection=$collection&img_url=$img_url" frameborder=0></iframe>
<script type="text/javascript">
$("#pic_upload_iframe").css("height", document.getElementById('pic_upload_iframe').contentDocument.body.offsetHeight);
</script>
<hr />
HTML;
}

function tpl_search_box($action) {
	$ci = &get_instance();
	$search_config = $ci->config->item('search');
	
	echo <<<HTML
<div class="search_box">
	<form id="search_box" method="get" action="$action">
		<input type="text" id="q" name="q" value="{$ci->query['q']}" />
		<input type="hidden" name="collection" value="{$ci->query['collection']}" />
HTML;
	if($ci->query['collection'] == 'Region') {
	    $config = $ci->config->item('region_field');
	    $search_config['category'] = $config['list_search_category'];
        echo <<<HTML
        <select name="search_field">
HTML;
    	foreach($search_config['category'] AS $collection) {
    		//$selected = $ci->query['collection'] == $collection ? 'selected="selected"' : '';
    		echo <<<HTML
    		<option value="$collection">$collection</option>
HTML;
    	}
    	echo <<<HTML
    		</select>
HTML;
	}
    echo <<<HTML
		<input type="button" value="搜索" onclick="$('#search_box').submit()" />
	</form>
</div>
HTML;
}

function tpl_table_lists($lists, $fields) {
	if(empty($lists)) {return '';}
	$ci = &get_instance();
	$width = (800 / count($fields)).'px';
	echo <<<HTML
<script type="text/javascript">
function itemDelete(type, id) {
	$.ajax({
    	type: "GET",
    	url: "/ajax/delete/" + type + "/" + id,
    	dataType: 'html',
    	success: function(sig) {
  			if(sig == '1') {
  				$('#'+id).fadeOut();
    		}
  		}
  	});
}
</script>
<div class="lists_wrapper">
	<style>._table_tr {width:$width;}</style>
	<table>
		<tr class="headline">
HTML;
    $fields['edit'] = '编辑';
    $fields['delete'] = "删除";
    
	foreach($fields AS $index => $name) {
		echo <<<HTML
			<td class="_table_tr">$name</td>
HTML;
	}
	echo '
		</tr>';
	foreach($lists AS $key => $value) {
		echo <<<HTML
		<tr id="{$value['_id']}">
HTML;
		foreach(array_keys($fields) AS $index) {
			$link = '';
			$content = isset($value[$index]) ? $value[$index] : '';
			if($index == 'edit') {
				$link = '/'.strtolower($ci->query['collection']).'/detail?_id='.$value['_id'];
				$content = $fields[$index];
			}
			if($index == 'poi_cnt' && $value['poi_cnt']) {
				$link = '/search/lists/?regionId='.$value['_id'].'&collection=POI';
			}
			if($index == 'area' && $ci->query['collection'] == 'POI' && !empty($value['regionId'])) {
				$link = '/search/lists/?regionId='.$value['regionId'].'&collection=POI';
			}
		    if($index == 'poi_pic' && $ci->query['collection'] == 'Region' ) {
				$link = '/images/poi/?regionId=' . $value['_id'];
				$content = '修改';
			}
		    if($index == 'center') {
				$content = (!empty($value[$index])) ? '存在' : '不存在';
			}
    		if ($ci->query['collection'] == 'POI' && $index == 'wikititle') {
    		    $wiki = get_wiki_content($value['wikititle']);
    		    $href = "/wikipedia/detail?_id={$wiki['_id']}";
    		    $content = sprintf('<a href="%s" target="_blank">%s</a>', $href, $value['wikititle']);
    		}
		    if($index == 'delete') {
		        $content = <<<HTML
		        	<a href="javascript::void(0);" onclick="itemDelete('{$ci->query['collection']}', '{$value['_id']}');">删除</a>
HTML;
			} else {
			    $content = !empty($link) ? "<a href=\"$link\" target=\"_blank\">$content</a>" : $content;
			}
			echo <<<HTML
			<td  class="_table_tr">$content</td>
HTML;
		}
		echo '
		</tr>';
	}
	echo '
	</table>
</div>';
}

function tpl_nav() {
	$ci = &get_instance();
	if(empty($ci->res['nav'])) {return '';}
	echo <<<HTML
<div class="nav">
	<div class="_nav_box">
HTML;
	foreach($ci->res['nav'] AS $key => $value) {
		$selected = '';
		!empty($ci->query['category']) && $ci->query['category'] == $value && $selected = '_nav_selected';
		$link = empty($selected) ? tpl_link_generater('/search/lists/', array('category' => $value)) : '#';
		echo <<<HTML
		<a href="$link" class="_nav_item $selected">
			$value
		</a>
HTML;
	}
	echo <<<HTML
	</div>
</div>
<hr />
HTML;
}

function tpl_link_generater($link, $reset_field, $query = array()) {
	$ci = &get_instance();
	empty($query) && $query = $ci->input->get();
	empty($query) && $query = array();
	$query = array_merge($query, $reset_field);
	$query = array_filter($query, 'trim');
	$tmp_query = array();
	foreach($query AS $name => $value) {
		$tmp_query[] = $name.'='.$value;
	}
	substr($link, -1) != '?' && $link .= '?';
	$link .= implode("&", $tmp_query);
	return $link;
}

function tpl_multi_page() {
	$ci = &get_instance();
	$link = tpl_link_generater('/search/lists/', array('pg' => null));
	$curpage = !empty($ci->query['pg']) ? $ci->query['pg'] : 1;
	$multi = multi($ci->res['count'], $ci->query['ps'], $curpage, $link);
	echo <<<HTML
<div class="page">
	$multi
</div>
<hr />
		
HTML;
}

function tpl_index() {
	$ci = &get_instance();
	$config = $ci->config->item('search');
	$category = $config['category'];
	echo <<<HTML
<div class="index">
	<ul>
HTML;
	foreach($category AS $value) {
		echo <<<HTML
		<a href="/search/lists/?collection=$value" target="_blank">
			<li>$value</li>
		</a>
HTML;
	}
	echo '
	</ul>
</div>'; 
}

function tpl_correlation($correlation) {
    $correlation_id = strval($correlation['_id']);
    $name = $correlation['name'];
    $ci = &get_instance();
    if(empty($correlation)) { return; }
    echo <<<HTML
    <div class="correlation">
    	<br/>
    	<h1>$name</h1>
    	<form id="correlation_form" method="post" action="/correlation/post">
    	<input name="_id" type="hidden" value="$correlation_id"/>
HTML;
    foreach ($correlation['correlation'] AS $field => $value) {
        $input_name = "$field"."[]";
        echo <<<HTML
        	<div class="correlation_field">
        	<h1>$field</h1>
HTML;
        foreach ($value AS $name => $score) {
            $id = md5($name.$score);
            $sub_score = number_format($score, 4, '.', '');
            echo <<<HTML
            <div class="correlation_checkbox">
            	<input id="$id" class="_checkbox" data="$input_name" type="checkbox" checked="checked"/>
            	<input id="$id-box" name="$input_name" type="hidden" value="$name:$score"/>
            	<label for="$id">$name:$sub_score</label>
            </div>
HTML;
        }
        echo <<<HTML
        	<div class="clear"></div>
        	</div>
HTML;
    }
    echo <<<HTML
    	<input id="main_submit" type="button" value="提交" onclick="$('#correlation_form').submit();"/>
    </div>
<script type="text/javascript">
$('._checkbox').click(function(){
	var id = $(this).attr('id');
	var name = $(this).attr('data');
	if($(this).attr('checked') == 'checked') {
		$('#'+id+'-box').attr('name', name);
	} else {
		$('#'+id+'-box').removeAttr('name');
	}
});
</script>
HTML;
}


