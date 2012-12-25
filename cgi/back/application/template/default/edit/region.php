<div id="common-canvas" class="edit region">
<form id="form-data" method="post" action="/edit/update/region">
  <div class="form-horizontal">
<?php 
foreach ($this['fields'] AS $field) {
  $value = $this->safeGet("detail.$field", '');
  switch ($field) {
    case 'is_important':
      $content = sprintf('<input name="%s" type="checkbox" id="input-%s" checked="%s"/>', 
                         $field, $field, 
                         !empty($value) ? 'checked' : '');
    break;
    case 'keyword':
      if (is_array($value)) $value = implode(", ", $value);
    default:
      $content =  sprintf('<input name="%s" type="text" id="input-%s" placeholder="%s" value="%s"/>', 
                           $field, $field, $field, $this->safeGet("detail.$field", ''));
    break;
  }
  echo '<div class="control-group">';
  echo sprintf('<label class="control-label" for="input-%s">%s</label>', $field, $field);
  echo   '<div class="controls">';
  echo     $content;
  echo   '</div>';
  echo '</div>';
}
?>
    <div class="clearfix"></div>
    <hr />
<?php 
$category = Mf::$mongo['tripfm']->command(
  array("distinct" => 'Region', "key" => 'category')
);
?>
    <div class="select-category">
      <span>分类</span>
      <select class="span2" name="category">
<?php 
foreach ($category['values'] as $value) {
  $select = ($this->safeGet('detail.category', '') == $value) ? 'selected="selected"' : '';
  echo sprintf('<option %s>%s</option>', $select, $value);
}
?>
      </select>
    </div>
  </div>
  <hr />
<?php 
$address = $this->safeGet("detail.address", "");
?>
  <div class="input-append map">
    <input id="search" name="address" class="span9" id="appendedInputButtons" placeholder="输入地址" type="text" value="<?=$address?>">
    <button id="search-button" class="btn" type="button">搜索</button>
  </div>
  <div id="maps"></div>
  <hr />
<?php 
$wiki_content = trim($this->safeGet('detail.wikiContent', ''));
if (empty($wiki_content)) {
  $wiki = g_get_wiki($this['detail.wikititle']);
  $wiki_content = (!empty($wiki['content']) ? $wiki['content'] : "");
}
?>
  <div class="textarea-wiki">
    <p>WikiContent</p>
    <textarea name="wikiContent"><?=$wiki_content?></textarea>
  </div>
  <hr />
  <div class="submit-area">
    <button id="edit-clear" class="btn">清除</button>
    <button id="edit-submit" class="btn btn-primary">提交</button> 
  </div>
<?php 
// map center
$center = $this->safeGet("detail.center", array(0, 0));
if (empty($center)) $center = array(0, 0);
$map_latlng = "{$center[0]}, {$center[1]}";
// map zoom
$map_zoom = $this->safeGet("detail.map_zoom", 12);
if (empty($map_zoom)) $map_zoom = 12;
?>
  <input name="_id" type="hidden" value="<?=$this['detail._id']?>"/>
  <input id="map_zoom" name="map_zoom" type="hidden" value="<?=$map_zoom?>"/>
  <input id="map_lat" name="center[0]" type="hidden" value="<?=$center[0]?>"/>
  <input id="map_lng" name="center[1]" type="hidden" value="<?=$center[1]?>"/>
</form>
</div>
<script type="text/javascript">
gMap.draw('maps', {
  zoom         : <?=$map_zoom?>,
  center       : new google.maps.LatLng(<?=$map_latlng?>),
  panControl   : true,
  zoomControl  : true,
  scaleControl : true,
  mapTypeId    : google.maps.MapTypeId.ROADMAP
}, {
  recordCenter : function (center) {
    $('#map_lat').val(center.lat());
    $('#map_lng').val(center.lng());
  },
  recordZoom   : function (zoom) {
    $('#map_zoom').val(zoom);
  }
});
$('#search-button').click(function(){
  gMap.search($('#search').val());
});
</script>
<script type="text/javascript">
$('#form-data').on('submit', function(e) {
  e.preventDefault(); // prevent native submit
});
//submit area
$('#edit-submit').click(function(){
  var buttons = $('.submit-area button');
  buttons.addClass('disabled');
  $('#form-data').ajaxSubmit({
    //data    : {domain:'t.cn'},
    dataType: 'json',
    success: function(data){
      //if ($('pre').length > 0) return $('pre').html(data);
      //$('<pre>').html(data).appendTo(document.body); return;
      if (data.code != 100) return alert(data.errorMsg + "\n更新失败,请联系管理员");
      alert("修改成功!")
    },
    error: function() {
      alert("Ajax Fail\n更新失败,请联系管理员");
    },
    complete: function() {
      buttons.removeClass('disabled');
    }
  });
});
$('#edit-clear').click(function(){
  window.location.reload();
});
</script>
