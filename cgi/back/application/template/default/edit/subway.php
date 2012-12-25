<style id="station-order-style">
.station-order .badge {
<?php 
$color = $this->safeGet("detail.color", '');
if (!empty($color)) {
  echo "background-color: $color;";
}
?>
}
</style>
<input id="regionId" type="hidden" value="<?=strval($this['detail.regionId'])?>"/>
<input id="subwayId" type="hidden" value="<?=strval($this['detail._id'])?>"/>
<input id="lineId" type="hidden" value="<?=strval($this['detail.lineId'])?>"/>
<div id="common-canvas" class="edit subway">
  <div class="form-horizontal">
<?php 
foreach ($this['fields'] AS $field) {
  echo '<div class="control-group pull-left">';
  echo sprintf('<label class="control-label" for="input-%s">%s</label>', $field, $field);
  echo   '<div class="controls">';
  echo sprintf('<input name="%s" type="text" id="input-%s" placeholder="%s" value="%s"/>', 
               $field, $field, $field, $this->safeGet("detail.$field", ''));
  echo   '</div>'.
       '</div>';
}
?>
    <div class="clearfix"></div>
  </div>
  <hr />
  <div class="container-fluid">
    <div class="row-fluid">
      <div id="station-box" class="span4 station-pool">
<?php 
foreach ($this['stations'] AS $station) {
  echo sprintf('<div class="handle-hover pool-station" pid="%s" flag="pool-station" title="%s">%s</div>',
               strval($station['_id']), $station['name'], $station['name']);
}
?>
      </div>
      <div class="span8 subline">
        <div class="input-append">
          <input id="add-input-box" class="span8" id="appendedInputButtons" placeholder="Add Station/Subline" type="text">
          <button id="add-station" class="btn" type="button">添加站点</button>
          <button id="add-subline" class="btn" type="button">添加子线</button>
        </div>
        <hr />
        <div class="tabbable tabs-right">
          <ul id="subline-name-box" class="nav nav-tabs">
<?php 
foreach ($this['detail.subline'] as $index => $subline) {
  $class = !$index ? 'active' : '';
  echo sprintf('<li class="%s handle-hover" flag="line-name">', $class);
  echo sprintf('<a href="#subline-%d" data-toggle="tab" title="%s">%s</a>', 
               $subline['id'], $subline['name'], $subline['name']);
  echo '</li>';
}
?>
          </ul>
          <div id="subline-station-box" class="tab-content">
<?php 
$direction = array(
  'icon-arrow-down', 
  'icon-resize-vertical', 
  'icon-arrow-up'
);
foreach ($this['detail.subline'] as $index => $subline) {
  $class = !$index ? 'active' : '';
  echo sprintf(
        '<div class="tab-pane %s subline-list" id="subline-%d">', $class, $subline['id']);
  foreach ($subline['list'] as $station) {
    $poi = Mf::$mongo['tripfm.POI']->findOne(array('_id' => $station['poiId']), array('name'));
    echo sprintf(
           '<div class="subline-station handle-hover" flag="line-station" pid="%s">', strval($station['poiId']));
    echo     '<div class="station-order">';
    echo sprintf('<span class="badge badge-info">%d</span>', $station['order']);
    echo     '</div>';
echo sprintf('<div class="station-name" title="%s">%s</div>', $poi['name'], $poi['name']);
echo sprintf('<div class="direction" direction="%s"><i class="%s"></i></div>',
             $station['direct'], $direction[$station['direct']+1]);
    echo   '</div>';
  }
  echo '</div>';
}
?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <hr />
  <div class="submit-area">
    <button id="edit-clear" class="btn">清除</button>
    <button id="edit-submit" class="btn btn-primary">提交</button> 
  </div>
  <div id="handle" style="display:none;position:absolute;">
    <a href="javascript:void(0);" title="编辑名称"><i class="icon-edit"></i></a>
    <a href="javascript:void(0);" title="跳转至POI页"><i class="icon-wrench"></i></a>
    <a href="javascript:void(0);" title="删除"><i class="icon-remove"></i></a>
  </div>
  <div id="change-direction" style="display:none;position:absolute;"><i class="icon-refresh"></i></div>
</div>
<script type="text/javascript">

// color change
$('#input-color').change(function(){
  var color = $(this).val();
  $('#station-order-style').html('.station-order .badge { background-color: ' + color + '; }');
});

var newSublineId = 1;

// add subline
$('#add-subline').click(function(){
  var name = $('#add-input-box').val();
  var name = $('#add-input-box').val();
  if (!name.length) return alert('Name 不能为空');
  var nameNode = $('<li>', {
        'class': 'handle-hover',
        'flag' : 'line-name'
      }).append(
        $('<a>', {
          'href'       : '#subline-new-' + newSublineId,
          'data-toggle': 'tab'
        }).html(name)
      );
  var lineNode = $('<div>', {
        'id'   : 'subline-new-' + newSublineId,
        'class': 'tab-pane'
      }).append($('<div>'));
  newSublineId ++;
  $('#subline-name-box').append(nameNode);
  $('#subline-station-box').append(lineNode);
});

// add station
$('#add-station').click(function(){
  var name = $('#add-input-box').val();
  if (!name.length) return alert('Name 不能为空');
  $.ajax({
    type: "POST",
    url: "/ajax/mongo/addSubwayStation",
    async : false,
    dataType: 'json',
    data: { data: {
      regionId : $('#regionId').val(),
      name     : name,
      lineId   : $('#lineId').val()
    }},
    error: function() {
      alert("Ajax Fail\n添加失败 请联系管理员");
    },
    success: function(response) {
      //if ($('pre').length > 0) return $('pre').html(response);
      //$('<pre>').html(response).appendTo(document.body); return;
      if (response.code != 100) return alert(response.errorMsg + "\n添加失败 请联系管理员");
      var station = $('<div>', {
        'class': 'handle-hover pool-station',
        'flag' : 'pool-station',
        'title': name,
        'pid'  : response.pid
      }).html(name);
      $('#station-box').append(station);
      drag.dragEnable(station, 'pool');
    }
  });
  
});

// make dragable
var drag = new Drag(
  '#station-box .pool-station', 
  '#subline-station-box .active .subline-station', 
  '#subline-station-box .active .subline-station',
  '.handle-hover'
);

// submit area
$('#edit-clear').click(function(){
  window.location.reload();
});


$('#edit-submit').click(function() {
  if ($(this).hasClass('disabled')) return;
  $(this).addClass('disabled');
  var _this = this;
  var data = {
    subwayId : $('#subwayId').val(),
    lineId   : $('#lineId').val(),
    station  : {},
    subline  : {},
    fields   : {}
  };
  $('input').each(function(i, el){
    var obj = $(el);
    if (!obj.attr('name') || obj.attr('name').trim().length <= 0) return true;
    data.fields[obj.attr('name')] = obj.val();
  });
  // station pool
  $('#station-box .pool-station').each(function(i, el){
    var pid = $(el).attr('pid');
    data.station[pid] = $(el).html();
  });
  
  // subline
  $('#subline-name-box li').each(function(i, el){
    var obj = $(el).find('a');
    var clickid = obj.attr('href').substr(1);
    var subline_id = clickid.substr(8);
    data.subline[subline_id] = {
      name : obj.html(),
      list : new Array()
    };
    
    $('#' + clickid).find('.subline-station').each(function(i, el){
      data.subline[subline_id].list.push({
        order  : $(el).find('.badge').html(),
        poiId  : $(el).attr('pid'),
        direct : $(el).children('.direction').attr('direction')
      });
    });
  });
  
  // ajax submit
  $.ajax({
    type: "POST",
    url: "/ajax/submit/subway",
    async : false,
    dataType: 'json',
    data: {data:data},
    success: function(data) {
      //if ($('pre').length > 0) return $('pre').html(data);
      //$('<pre>').html(data).appendTo(document.body); return;
      if (data.code != 100) return alert(data.errorMsg + "\n更新失败,请联系管理员");
      alert("修改成功!")
    },
    error: function() {
      alert("Ajax Fail\n更新失败,请联系管理员");
    },
    complete: function() {
      $(_this).removeClass('disabled');
    }
  });
  
});
</script>

