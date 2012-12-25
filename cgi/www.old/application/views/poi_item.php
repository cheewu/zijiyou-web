<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf8" />
	<title><?=$result['name']?>--POI管理后台</title>
	<script type="text/javascript" src="/js/jquery.min.js"></script>
	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
	<link href="/style/css.css" rel="stylesheet" type="text/css" />
</head>

<body>
	<div id="travel_background">
		<form name="editForm" id="editForm" action="/<?=$type ? 'user/creat_collection/' : 'poi/save' ?>" method="post">
			<?=$type ? '<input type="hidden" name="collect_type" value="poi"/>' : '' ?>
			<input type="hidden" name="_id" value="<?=$result['_id']?>"/>
			<input type="hidden" name="pg" value="<?=$pg?>"/>
			<input type="hidden" name="lat" id="lat" value="<?=$result['center'][0]?>"/>
			<input type="hidden" name="lng" id="lng" value="<?=$result['center'][1]?>"/>
			<div id="box">
				<div class="lv_center">
					<h1>详情</h1>
					<ol>
						<dt>区域</dt>
						<dd><input name="area" type="text" class="lv_field" value="<?=$result['area']?>"/></dd>
					</ol>
					<ol>
						<dt>名称</dt>
						<dd><input name="name" type="text" class="lv_field" value="<?=$result['name']?>"/></dd>
					</ol>
					<ol>
						<dt>英文名</dt>
						<dd><input name="englishName" type="text" class="lv_field" value="<?=$result['englishName']?>"/></dd>
					</ol>
					<ol>
						<dt>关键词</dt>
						<dd><input name="keyword" type="text" class="lv_field" value="<?=$result['keyword']?>"/></dd>
					</ol>
					<ol>
						<dt>门票</dt>
						<dd><input name="ticket" type="text" class="lv_field" value="<?=$result['ticket']?>"/></dd>
					</ol>
					<ol>
						<dt>电话</dt>
						<dd><input name="telNum" type="text" class="lv_field" value="<?=$result['telNum']?>"/></dd>
					</ol>
					<ol>
						<dt>营业时间</dt>
						<dd><input name="openTime" type="text" class="lv_field" value="<?=$result['openTime']?>"/></dd>
					</ol>
					<ol>
						<dt>网站</dt>
						<dd><input name="website" type="text" class="lv_field" value="<?=$result['website']?>"/></dd>
					</ol>
					<ol>
						<dt>微博</dt>
						<dd><input name="weibo" type="text" class="lv_field" value="<?=$result['weibo']?>"/></dd>
					</ol>
					<ol>
						<dt>博客</dt>
						<dd><input name="blog" type="text" class="lv_field" value="<?=$result['blog']?>"/></dd>
					</ol>
					<ol>
						<dt class="lv_field_area_box">描述</dt>
						<dd><textarea name="desc" class="lv_field_area"><?=strip_tags($result['desc'])?></textarea></dd>
					</ol>
					<ol>
						<dt>类别</dt>
						<dd><select name="category" id="category">
								<?php foreach($category as $key => $value) {?>
									<option <?php if($result['category'] == $key) {echo 'selected';}?> value='<?=$key?>'><?=$value?></option>
								<?php }?>
							</select>
						</dd>
					</ol>
					<h1>位置</h1>
					<ol>
						<dt>搜索地图坐标</dt>
						<dd>
							<input id="address" name="address_input" value="<?=$result['address']?>" />
							<input id="places" name="address" value="<?=$result['address']?>" type="hidden"/>
							<input type="button" value="搜索" onclick="javascript:codeAddress();"/>
						</dd>
					</ol>
					<ol>
						<dt>入口位置</dt>
						<dd>
							<div id="map_canvas" style="width:600px;height:400px"></div>
							<p><label> <input type="checkbox" name="checkbox" value="checkbox" /> </label> 在完成修改审核后或需要更多信息时发送电子邮件。</p>
							<div class="lv_button"><label> <input type="button" class="button" value="<?=$type ? '收藏' : '发布'?>" onclick="javascript:<?=$type ? 'toCollection()' : 'toSave()'?>;"/> </label>
								<label><input type="button" class="button" value="返回" onclick="javascript:<?=$type ? 'parent.$.fancybox.close();' : 'toBack();'?>"/> </label>		
								<?php if(!$type) {?>
								<label><input type="button" class="button" value="删除" onclick="javascript:toDelete('<?=$result['_id']?>');"/></label>
								<?php }?>
							</div>
						</dd>
					</ol>
				</div>
			</div>
		</form>
		<div class="travel_bottomnavbg"></div>
	</div>
</body>
<script type="text/javascript">
  var geocoder;
  var map;
  var markers; //记录marker的变量
  <?php if($result['center'][0]) {?>
  var latlng = new google.maps.LatLng(<?=$result['center'][0]?>, <?=$result['center'][1]?>);
  <?php }?>
  function initialize() {
    geocoder = new google.maps.Geocoder();
    var myOptions = {
      zoom: 14,
      panControl: true,
      zoomControl: true,
      scaleControl: true,
      <?php if($result['center'][0]) {?>
      center: latlng,
      <?php }?>
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions); //添加click事件，为地图添加marker
    google.maps.event.addListener(map, 'click', function(event) {
        addMarker(event.latLng);
    });
  }

  function addMarker(location) {
	    marker = new google.maps.Marker({
	        position: location,
	        map: map
	      });
	      if(markers){//清除之前标记
	    	  markers.setMap(null);
	      }
	      markers = marker;
	      changePosizition();
    }
  
  function codeAddress() {
    var address = $("#address").val();
    geocoder.geocode( { 'address': address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
	    if(markers){ //清除之前标记
	    	markers.setMap(null);
	    }
        map.setCenter(results[0].geometry.location);
        var marker = new google.maps.Marker({
            map: map, 
            position: results[0].geometry.location
        });
        markers = marker;
        $("#places").val(address);
        changePosizition();
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }

  function changePosizition(){//改变Input lat与lng字段
	  $("#lat").val(markers.position.lat());
	  $("#lng").val(markers.position.lng());
	  }
  
  function toCollection() {
		var cate = $('input[name="collect_type"]').val();
		var id = $('input[name="_id"]').val();
		$.ajax({
			'type': "GET",
			'url': "/user/ajax_collection_check/",
			'dataType': 'text',
			'data': 'cate=' + cate + '&id=' + id,
			'success': function(sig){
				if(sig == 'notlogin'){
					window.location = '/user/login';
				}else if(sig == 'collected'){
					alert('您已经收藏过此项目，请勿再次收藏');
				}else{
					$('#editForm')[0].submit();
				}
			}
		});
  }
  function toSave() {
	  $('#editForm')[0].submit();
  }
  function toBack() {
	  window.location = "/poi/lists/<?=$pg.$append_url?>";
  }
  function toDelete(id){
	  $.ajax({
			'type': "GET",
			'url': "/poi/delete",
			'dataType': 'text',
			'data': 'id=' + id,
			'success': function(sig){
				if(sig == 'fail'){
					alert('删除失败，请联系管理员');
				}
				if(sig == 'success'){
					window.location = "/poi/lists/<?=$pg.$append_url?>";
				}
			}
	   });
  }
</script>

<script type="text/javascript">
	$(document).ready(
			function() {
				initialize();
				<?php if(!$result['center'][0]) {?>
				address = '<?=$result['name']?>';
			    geocoder.geocode( { 'address': address}, function(results, status) {
			        if (status == google.maps.GeocoderStatus.OK) {
			          map.setCenter(results[0].geometry.location);
			          var marker = new google.maps.Marker({
			              map: map, 
			              position: results[0].geometry.location
			          });
			          markers = marker;
			          changePosizition();
			        } else {
			          alert("Geocode was not successful for the following reason: " + status);
			        }
			      });
			     <?php }else{?>
				     var marker = new google.maps.Marker({ //如果坐标存在，则直接显示标记
			              map: map, 
			              position: latlng
			          });
				     markers = marker;
			     <?php } ?>
			}
				
	);
</script>
</html>
