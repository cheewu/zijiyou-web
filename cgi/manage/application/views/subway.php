<?php include 'header.php'; ?>
<form id="data-from" action="/subway/post" method="post">
<input id="region" value="<?=$subway['region']?>" type="hidden"/>
<input id="regionId" value="<?=$subway['regionId']?>" type="hidden"/>
<input name="lineid" id="lineid" value="<?=$subway['lineid']?>" type="hidden"/>
<input name="_id" value="<?=$subway['_id']?>" type="hidden"/>
<?php 
foreach ($this->fields AS $field) {
  if ($field == 'stationList') continue;
  $diable = ''; $suffix = '';
  if (in_array($field, array('lineid', 'numberOfStation'))) {
    $diable = 'disabled="disabled"';
  }
  if (in_array($field, array('numberOfStation'))) { 
    $suffix = '(动态计算)';
  }
  if ($field == 'wiki') {
    echo <<<HTML
  <div class="input_text">
    <span class="_input_text_name">$field:</span>
    <a href="{$subway[$field]}" target="_blank">{$subway[$field]}</a>
  </div>
HTML;
    continue;
  }
  echo <<<HTML
  <div class="input_text">
    <span class="_input_text_name">{$field}{$suffix}:</span>
    <input name="$field" $diable value="{$subway[$field]}"/>
  </div>
HTML;
}
?>
<script type="text/javascript">
$("input[name='color']").bind('keyup keydown keypress', function(){
  $(this).siblings('._input_text_name').css('color', this.value);
});
var stationField = <?=json_encode($this->station_fields);?>;
</script>
<hr />

<style type="text/css">
.drag-area, .drag-area-popup { 
  width:786px; margin-bottom:10px; cursor:default; 
  font-size:10px; font-weight:bold; text-align:left;
  border: 2px groove threedface; padding:5px;
  position:relative;
}
.drag-me { 
  font-size:50px; text-decoration:none; 
  position:absolute; left:5px; top:0;
}
.delete-me {
  font-size:35px; text-decoration:none; 
  position:absolute; right:-15px; top:-30px;
}
.drag-me:hover, .delete-me:hover {  text-decoration:none; cursor:pointer; }
/*.drag-area-popup { color:#7f7f7f; }*/
.field-set { 
  width:800px; padding:10px; cursor:default;
  margin:30px auto;
}
fieldset { border:2px groove threedface; }
.station-arrow { text-align:center; font-size:50px;}
.station-detail { width:380px; display:inline-block; }
.station-detail span, .station-detail input { width: 200px; display:inline-block; }
.station-detail span { text-align:right; width:150px;} 

#add-detail-submit { margin:10px auto 0 auto; display:block; width:100px; }

</style>
<fieldset class="field-set">
  <legend>添加</legend>
<?php 
foreach ($this->station_fields AS $field) {
  if ($field == 'poiId') continue;
  $value = $hidden = "";
  $title = $field;
  if ($field == 'transferLine') {
    $title .= '(json)'; $value = '[]';
  }
  if ($field == 'stationOrder') {
    $hidden = 'style="display:none"';
  }
  echo <<<HTML
  <div class="station-detail" $hidden>
    <span>$title:</span>
    <input $hidden class="add-detail" field="$field" value="$value"/>
  </div>
HTML;
}
?>
  <input class="add-detail" field="poiId" value="" type="hidden"/>
  <input id="add-detail-submit" type="button" value="添加"/>
</fieldset>
<fieldset id="drag-box" class="field-set">
  <legend>Line Order 点击圆点以拖动</legend>
<?php
$station_html = array();
foreach ($subway['stationList'] AS $station) {
  //$json_data = str_replace('"', "'", json_unicode_to_utf8(json_encode($station)));
  $stationOrder = $station['stationOrder'];
  $html = <<<HTML
  <div class="drag-area">
    <a title="click to drag" class="drag-me">&bull;</a>
    <a title="click to delete" class="delete-me">&otimes;</a>
HTML;
  foreach ($this->station_fields AS $key) {
    $value = isset($station[$key]) ? $station[$key] : '';
    $suffix = "";
    if ($key == 'poiId') {
      $html .= <<<HTML
    <div class="station-detail">
      <span>{$key}{$suffix}:</span>
      <a href="http://manage.zijiyou.com/poi/detail?_id=$value" target="_blank">$value</a>
      <input class="poi-id" name="stationList[$stationOrder][$key]" value="$value" type="hidden"/>
    </div>
HTML;
      continue;
    }
    if ($key == 'stationOrder') {
      $html .= <<<HTML
    <div class="station-detail">
      <span>{$key}{$suffix}:</span>
      <input value="$value" disabled="disabled"/>
      <input name="stationList[$stationOrder][$key]" value="$value" type="hidden"/>
    </div>
HTML;
      continue;
    }
    if ($key == 'transferLine') {
      $value = json_encode($value);
      $suffix = '(json)';
    }
    $html .= <<<HTML
    <div class="station-detail">
      <span>{$key}{$suffix}:</span>
      <input name="stationList[$stationOrder][$key]" value="$value"/>
    </div>
HTML;
  }
  $html .= <<<HTML
  </div>
HTML;
  $station_html[] = $html;
} 
echo implode("<div class='station-arrow target'>&darr;</div>\n", $station_html);
?>
</fieldset>
<div class="clear"></div>
<input id="main_submit" type="button" value="提交" onclick="$('#data-from').submit();"/>
</form>
<script type="text/javascript">
Number.prototype.NaN0 = function() { return isNaN(this) ? 0 : this; }
// for ie
if (typeof document.getElementsByClassName != 'function') {
  document.getElementsByClassName = function(elementClass) {
    var selectElement = [];
    var classReg = new RegExp('\\b' + elementClass + '\\b');
    var elements = this.getElementsByTagName("*");
    for(var i in elements) {
      if (classReg.test(elements[i].className)) {
        selectElement.push(elements[i]);
      }
    }
  }
}
document.onmousemove = mouseMove;
document.onmouseup   = mouseUp;

var draggableClass   = 'drag-me';
var targetClass      = 'target';
var dragObject       = null;
var curObject        = null;
var curObjectPos     = null;
var mouseOffset      = null;
var iMouseDown       = false;

window.onload = function() {
  var draggableObjects = document.getElementsByClassName(draggableClass);
  for (var i in draggableObjects) {
    makeDraggable(draggableObjects[i]);
  }
  var deleteObjects = document.getElementsByClassName('delete-me');
  for (var i in deleteObjects) {
    makeDeletable(deleteObjects[i]);
  }
}

function mouseUp(){
  if (!iMouseDown) return;
  dragObject.parentNode.removeChild(dragObject);
  curObject.style.visibility = '';
  dragObject = null;
  iMouseDown = false;
  reOrder();
}

function mouseMove(ev){
  ev           = ev || window.event;
  var mousePos = getMousePos(ev);
  /*
  We are setting target to whatever item the mouse is currently on
  Firefox uses event.target here, MSIE uses event.srcElement
  var target = ev.target || ev.srcElement;
  */
  if (!dragObject) return;
  setDragPosition(ev);
  var left   = mousePos.x - mouseOffset.x;
  var top    = mousePos.y - mouseOffset.y;
  var height = curObject.offsetHeight;
  var bottom = top + height;
  var middle = top + parseInt(height / 2); 
    
  var targetObjects = document.getElementsByClassName(targetClass);
  var curObjectPos  = getPosition(curObject);
  
  for (var i in targetObjects) {
    var v = targetObjects[i]; 
    var pos = getPosition(v);
    var mid = pos.y + parseInt(v.offsetHeight / 2);
    var beforeNode = null;
    if (pos.y == curObjectPos.y && pos.x == curObjectPos.x) continue;
    if (mid < bottom && mid > top) { 
      if (top > curObjectPos.y && mid > middle) return;
      if (top < curObjectPos.y && mid < middle) return;
      beforeNode = v; break; 
    }
  }
  
  if (beforeNode) {
    var parent = curObject.parentNode;
    var changeNode = (top > curObjectPos.y) ? beforeNode.nextSibling : 
                                              beforeNode.previousSibling;
    // for none ie
    if (changeNode.nodeName == '#text' && !document.all && top > curObjectPos.y) {
      changeNode = changeNode.nextSibling;
    }
    var cloneNode = curObject.cloneNode(true);
    parent.insertBefore(cloneNode, curObject);
    parent.removeChild(curObject);
    parent.insertBefore(curObject, changeNode);
    parent.removeChild(changeNode);
    parent.insertBefore(changeNode, cloneNode);
    parent.removeChild(cloneNode);
  }
}

function makeDraggable(item){
  if (!item) return;
  item.onmousedown = function(ev){
    curObject   = item.parentNode;
    dragObject  = curObject.cloneNode(true);
    mouseOffset = getMouseOffset(curObject, ev);
    dragObject.className = 'drag-area-popup';
    curObject.style.visibility = 'hidden';
    document.getElementById('wrapper').appendChild(dragObject);
    setDragPosition(ev);
    iMouseDown = true;
    return false;
  }
}

function getMousePos(ev){
  if(ev.pageX || ev.pageY){
    return {x:ev.pageX, y:ev.pageY};
  }
  return {
    x:ev.clientX + document.body.scrollLeft - document.body.clientLeft,
    y:ev.clientY + document.body.scrollTop  - document.body.clientTop
  };
}

function getMouseOffset(target, ev){
  ev           = ev || window.event;
  var docPos   = getPosition(target);
  var mousePos = getMousePos(ev);
  return {x:mousePos.x - docPos.x, y:mousePos.y - docPos.y};
}

function getPosition(e){
  var left = 0;
  var top  = 0;
  do {
    // currentStyle for IE
    left += e.offsetLeft + (e.currentStyle ? (parseInt(e.currentStyle.borderLeftWidth)).NaN0() : 0);
    top  += e.offsetTop  + (e.currentStyle ? (parseInt(e.currentStyle.borderTopWidth)).NaN0() : 0);
    if (!(e = e.offsetParent)) break;
  } while (1);
  return {x:left, y:top};
}

function setDragPosition(ev) {
  ev           = ev || window.event;
  var mousePos = getMousePos(ev);
  dragObject.style.position = 'absolute';
  dragObject.style.top      = mousePos.y - mouseOffset.y + "px";
  dragObject.style.left     = mousePos.x - mouseOffset.x + "px";
}

function reOrder() {
  var regex = new RegExp('(stationList\\[)\\d+(\\].+)');
  var draggableObjects = document.getElementsByClassName(draggableClass);
  var inputObjects = document.getElementsByTagName('input');
  for (var i in inputObjects) {
    if (inputObjects[i].name == 'numberOfStation') {
      inputObjects[i].value = draggableObjects.length;
    }
  }
  for (var i in draggableObjects) {
    if (!draggableObjects[i].className) continue;
    var children = draggableObjects[i].parentNode.children;
    for (var j in children) {
      var v = children[j];
      if (v.tagName != 'DIV') continue;
      for (var k in v.children) {
        if (v.children[k].tagName != 'INPUT') continue;
        input = v.children[k];
        if (v.children[0].innerHTML == 'stationOrder:') input.value = i;
        input.name = input.name.replace(regex, '$1' + i + '$2');
      }
    }
  }
}

document.getElementById('add-detail-submit').onclick = function() {
  var addDetail    = document.getElementsByClassName('add-detail');
  var stations     = document.getElementsByClassName('drag-area')
  var stationOrder = stations.length || 0;
  var addElement   = document.createElement('div');
  addElement.className = 'drag-area';
  var drag = document.createElement('a');
  drag.title = 'click to drag';
  drag.className = 'drag-me';
  drag.innerHTML = '&bull;';
  var del = document.createElement('a');
  del.title = 'click to delete';
  del.className = 'delete-me';
  del.innerHTML = '&otimes;'
  addElement.appendChild(drag);
  addElement.appendChild(del);
  var insert = {};
  var fields = stationField;
  var poiIdInput;
  var poiIdLink;
  var poiTransferLine;
  for (var i in fields) {
    var field = fields[i]; 
    if (typeof field != 'string') continue;
    var fieldHtml = document.createElement('div');
    fieldHtml.className = 'station-detail';
    var span = document.createElement('span');
    span.innerHTML = field + ':';
    if (field == 'transferLine') {
      span.innerHTML = field + '(json):';
    }
    // append span
    fieldHtml.appendChild(span);
    // handle input
    var input = document.createElement('input');
    input.name = "stationList[" + stationOrder + "][" + field + "]";
    for (var j in addDetail) {
      var v = addDetail[j];
      if (v.className != 'add-detail') continue;
      if (v.getAttribute('field') == field) {
        input.value = v.value;
      }
    }
    if (field == 'poiId') {
      input.type = 'hidden';
      input.className = 'poi-id';
      poiIdInput = input; 
      poiIdLink = document.createElement('a');
      poiIdLink.target = "_blank";
      fieldHtml.appendChild(poiIdLink);
    }
    insert[field] = input.value;
    if (field == 'transferLine') {
      poiTransferLine = input;
    }
    if (field == 'stationOrder') {
      input.type  = 'hidden';
      var input_disabled = document.createElement('input');
      input.value = stationOrder;
      input_disabled.value = input.value;
      input_disabled.setAttribute('disabled', 'disabled');
      fieldHtml.appendChild(input_disabled);
    }
    fieldHtml.appendChild(input);
    addElement.appendChild(fieldHtml);
  }
  insert.region = document.getElementById('region').value;
  insert.lineid = document.getElementById('lineid').value;
  insert.regionId = document.getElementById('regionId').value;

  document.getElementById('drag-box').appendChild(addElement);
  
  var flag = false;
  $.ajax({
    type: "GET",
    url: "/subway/add_station/",
    async : false,
    dataType: 'json',
    data: insert,
    success: function(sig) {
      if (sig.response_code == '200') {
        alert('[错误]:' + sig.response_msg); return;
      }
      poiIdInput.value = sig.poiId;
      poiTransferLine.value = "[" + sig.line + "]";
      poiIdLink.href = "http://manage.zijiyou.com/poi/detail?_id=" + sig.poiId;
      poiIdLink.innerHTML = sig.poiId;
      flag = true
    }
  });
  if (!flag) return;
  if (stationOrder > 0) {
    var arrow = document.createElement('div');
    arrow.className = 'station-arrow target';
    arrow.innerHTML = '&darr;';
    document.getElementById('drag-box').appendChild(arrow);
  }
  makeDraggable(drag);
  makeDeletable(del);
  document.getElementById('drag-box').appendChild(addElement);
  reOrder();
  
}

function makeDeletable(item) {
  if (!item) return;
  item.onclick = function() {
    var parent = this.parentNode;
    var id = parent.getElementsByClassName('poi-id')[0].value;
    var lineid = document.getElementById('lineid').value;
    var flag = false;
    $.ajax({
      type: "GET",
      url: "/subway/delete_station/" + id + '/' + lineid,
      async : false,
      dataType: 'json',
      success: function(sig) {
        if (sig.response_code == '200') {
          alert('[错误]:' + sig.response_msg); return;
        }
        flag = true;
      }
    });
    if (!flag) return;
    var previous = parent.previousSibling;
    var next = parent.nextSibling;
    if (previous.nodeName == '#text') previous = previous.previousSibling;
    if (next && next.nodeName == '#text') next = next.nextSibling;
    if (previous && $(previous).hasClass(targetClass)) {
      previous.parentNode.removeChild(previous);
    } else if(next && $(next).hasClass(targetClass)) {
      next.parentNode.removeChild(next);
    }
    $(parent).fadeOut('fast', function(){
      parent.parentNode.removeChild(parent);
      reOrder();
    });
  }
}
</script>
<?php include 'footer.php'; ?>