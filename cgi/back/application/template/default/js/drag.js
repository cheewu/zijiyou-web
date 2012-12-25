function getNew(tag)
{
  return $(document.createElement(tag));
}

Number.prototype.NaN0 = function () { return isNaN(this) ? 0 : this; }
$.prototype.positions = function () {
  var e = this.get(0);
  var x = 0;
  var y = 0;
  do {
    // currentStyle for IE
    x += e.offsetLeft + (e.currentStyle ? (parseInt(e.currentStyle.borderLeftWidth)).NaN0() : 0);
    y += e.offsetTop  + (e.currentStyle ? (parseInt(e.currentStyle.borderTopWidth)).NaN0() : 0);
    if (!(e = e.offsetParent)) break;
  } while (1);
  return {left:x, top:y};
}

/**
 * jquery的appendChild不是引用插入, 很奇怪
 */
$.prototype.appendChild = function ($child) {
  var parent = this.get(0);
  if ($child instanceof jQuery) {
    parent.appendChild($child.get(0));
  } else {
    parent.appendChild($child);
  }
  return this;
}

/**
 * jquery的remove后会对该标签做一些额外处理
 */
$.prototype.removeSelf = function () {
  var obj = this.get(0);
  obj.parentNode.removeChild(obj);
  return this;
}

/**
 * jquery的preappend不是引用插入, 很奇怪
 */
$.prototype.insBefore = function ($child, $sibling) {
  var parent = this.get(0);
  if ($sibling instanceof jQuery) {
    parent.insertBefore($child.get(0), $sibling.get(0));
  } else {
    parent.insertBefore($child.get(0), $sibling);
  }
  return this;
}

/**
 * exchange
 */
$.prototype.exchange = function ($childA, $childB) {
  var parent = this.get(0);
  var childA = ($childA instanceof jQuery) ? $childA.get(0) : $childA;
  var childB = ($childB instanceof jQuery) ? $childB.get(0) : $childB;
  var Aclone = childA.cloneNode(true);
  var Bclone = childB.cloneNode(true);
  parent.insertBefore(Aclone, childB);
  parent.insertBefore(Bclone, childA);
  parent.removeChild(childA);
  parent.removeChild(childB);
  parent.insertBefore(childA, Aclone);
  parent.insertBefore(childB, Bclone);
  parent.removeChild(Aclone);
  parent.removeChild(Bclone);
}

/**
 * drag html class
 */
function Drag(poolSt, lineSt, target, hoverSelect) {
  this.lineBox  = '#subline-station-box .active';
  this.poolSt   = poolSt;
  this.lineSt   = lineSt;
  this.target   = target;
  this.hoverSe  = hoverSelect;
  this.handle   = $('#handle');
  this.handleDi = $('#change-direction');
  this.initVar().initEvent().initHover().initHandle();
}

Drag.prototype.initVar = function () {
  this.hoverEl   = null;
  this.showEl    = null;
  this.focusEl   = {obj: null, typ: null};
  this.poseEl    = {obj: null, dis: false};
  return this;
}

Drag.prototype.initEvent = function () {
  var _this = this;
  $(this.poolSt).mousedown(function(ev){
    _this.ev = ev; // || window.event;
    _this.dragInit(this, 'pool');
  });
  $(this.lineSt).mousedown(function(ev){
    _this.ev = ev; // || window.event;
    _this.dragInit(this, 'line');
  });
  $(document).mousemove(function(ev) {
    _this.ev = ev; // || window.event;
    _this.dragMove();
  });
  $(document).mouseup(function(ev) {
    _this.ev = ev; // || window.event;
    _this.dragDisable()
         .initHover();
  });
  return this;
}

Drag.prototype.initHover = function () {
  _this = this;
  $(this.hoverSe).hover(function(){ // move in
    if(_this.focusEl.obj) return
    _this.hoverEl = $(this); 
    var flag = _this.hoverEl.attr('flag');
    if (flag == 'line-name') {
      _this.handle.find('.icon-wrench').css('display', 'none');
    } else {
      _this.handle.find('.icon-wrench').css('display', '');
    }
    var pos = $(this).positions();
    _this.handle.css('display', '')
                .css('left', pos.left + $(this).width() - 3 + 'px')
                .css('top', pos.top + ($(this).height() - _this.handle.height()) / 2 + 'px');
    if (flag == 'line-station') {
      _this.handleDi.css('display', '')
                    .css('left', pos.left - _this.handleDi.outerWidth() + 'px')
                    .css('top', pos.top + ($(this).height() - _this.handleDi.outerHeight()) / 2 + 'px');
      var direcOrder = ['icon-arrow-down', 'icon-resize-vertical', 'icon-arrow-up'];
      _this.handleDi[0].onclick = function(){
        var direc = _this.hoverEl.find('.direction');
        var i = parseInt(direc.attr('direction')) + 1;
        direc.attr('direction', (i == 2 ? -1 : i))
             .children('i').attr('class', direcOrder[(i == 2 ? 0 : i + 1)]);
      };
    }
  }, function(){ // move out
    _this.handle.css('display', 'none');
    _this.handleDi.css('display', 'none');  
  });
  $(this.handle).hover(function(){ // move in
    if(_this.focusEl.obj) return this;
    $(this).css('display', '');
  }, function(){ // move out
    $(this).css('display', 'none');
  });
  $(this.handleDi).hover(function(){ // move in
    if(_this.focusEl.obj) return this;
    $(this).css('display', '');
  }, function(){ // move out
    $(this).css('display', 'none');
  });
  return this;
}

Drag.prototype.initHandle = function () {
  var _this = this;
  this.handle.find('.icon-remove').click(function(){
    if (!_this.hoverEl) return this;
    
    var flag = _this.hoverEl.attr('flag');
    if (flag == 'line-station') {
      _this.handle.css('display', 'none');
      var pid = _this.hoverEl.attr('pid');
      var sublineId = _this.hoverEl.parent('div').attr('id').substr(8);
      var lineId = $('#lineId').val();
      // no ajax
      _this.hoverEl.fadeOut().remove();
      _this.calcLineOrder();
      
      // use ajax
      /*
      if (sublineId.substr(0, 4) == 'new-') {
        return _this.hoverEl.fadeOut().remove();
      }
      $.ajax({
        type: "POST",
        url: "/ajax/mongo/removeStationFromSubLine",
        async : true,
        dataType: 'json',
        data: {data:{
          lineId    : lineId,
          sublineId : sublineId,
          poiId     : pid,
          order     : _this.hoverEl.find('.badge').html()
        }},
        success: function(data) {
          if (data.code != 100) return alert(data.errorMsg + "\n删除失败, 请联系管理员");
          _this.hoverEl.fadeOut().remove();
          _this.calcLineOrder();
        },
        error: function() {
          alert("Ajax Error\n删除失败, 请联系管理员");
        }
      });
      */
    }
    if (flag == 'pool-station') {
      var pid = _this.hoverEl.attr('pid');
      _this.handle.css('display', 'none');
      // no ajax
      $("div[pid='" + pid + "']").fadeOut().remove();
      
      // try ajax
      /*
      var pid = _this.hoverEl.attr('pid');
      var lineId = $('#lineId').val();
      var pid = _this.hoverEl.attr('pid');
      $.ajax({
        type: "POST",
        url: "/ajax/mongo/removeStationFromLine",
        async : true,
        dataType: 'json',
        data: {data:{
          lineId    : lineId,
          poiId     : pid
        }},
        success: function(data) {
          if (data.code != 100) return alert(data.errorMsg + "\n删除失败, 请联系管理员");
          $("div[pid='" + pid + "']").fadeOut().remove();
        },
        error: function() {
          alert("Ajax Error\n删除失败, 请联系管理员");
        }
      });
      */
    }
    if (flag == 'line-name') {
      var clickid = _this.hoverEl.find('a').attr('href').substr(1);
      _this.hoverEl.fadeOut().remove()
      $('#' + clickid).remove();
      if (!parent) return;
    }
  });
  return this.editHandle();
}

Drag.prototype.editHandle = function () {
  var _this = this;
  this.handle.find('.icon-edit').click(function(){
    if (!_this.hoverEl) return this;
    _this.editEl = _this.hoverEl;  // 记录当前点击的作用元素
    var inputBox = '#add-input-box';
    var flag = _this.hoverEl.attr('flag');
    var button = $('<button>', {
      'id'   : 'edit-button', 
      'class': 'btn', 
      'type' : 'button',
      'click': function() {
        var inputVal = $(inputBox).val().trim();
        if (flag == 'line-station' || flag == 'pool-station') {
          if (inputVal.length == 0) return alert('不能为空');
          else {
            // no ajax
            var pid = _this.editEl.attr('pid');
            $(".subline-station[pid='" + pid + "']").find('.station-name')
              .html(inputVal);
            $(".pool-station[pid='" + pid + "']").html(inputVal);
            
            // try ajax
            /*
            $.ajax({
              type: "POST",
              url: "/ajax/mongo/updateStationName",
              async : false,
              dataType: 'json',
              data: {data:{
                poiId : _this.editEl.attr('pid'),
                name  : inputVal
              }},
              error: function() {
                alert("Ajax Fail\n修改失败, 请联系管理员");
              },
              success: function(data) {
                if (data.code != 100) return alert(data.errorMsg + "\n修改失败, 请联系管理员");
                var pid = _this.editEl.attr('pid');
                $(".subline-station[pid='" + pid + "']").find('.station-name')
                  .html(inputVal);
                $(".pool-station[pid='" + pid + "']").html(inputVal);
              }
            });
          */
          }
          // end else
          _this.editEl.removeClass('editing');
        }
        if (flag == 'line-name') {
          _this.editEl.children('a')
                      .removeClass('editing');
          if (inputVal.length > 0) { 
            _this.editEl.children('a').html(inputVal);
          } else {
            alert('不能为空');
          }
        }
        button.remove();
        $('#add-input-box').removeClass('span9').addClass('span8');
        $('#add-station, #add-subline').show();
        _this.handle.css('visibility', '');
        $(inputBox).val('');
      }
    });
    button.html('修改名称');
    if (flag == 'pool-station') {
      $(inputBox).val(_this.editEl.html());
      _this.editEl.addClass('editing');
    }
    if (flag == 'line-station') {
      $(inputBox).val(_this.editEl.find('.station-name').html());
      _this.editEl.addClass('editing');
    }
    if (flag == 'line-name') {
      $(inputBox).val(_this.editEl.children('a').html());
      _this.editEl.children('a').addClass('editing');
    }
    _this.handle.css('visibility', 'hidden');
    $('#add-station, #add-subline').hide();
    $('.input-append').append(button);
    $('#add-input-box').removeClass('span8').addClass('span9');
  });
  return this.initLinkHandle();
}

Drag.prototype.initLinkHandle = function () {
  var _this = this;
  this.handle.find('.icon-wrench').click(function(){
    var poiId = _this.hoverEl.attr('pid');
    window.open('/edit/poi/?id=' + poiId);
  });
  return this;
}

Drag.prototype.dragEnable = function (item, type) {
  var _this = this;
  $(item).mousedown(function(ev){
    _this.ev = ev; // || window.event;
    _this.dragInit(this, type);
  });
  return this.initHover();
}

Drag.prototype.dragInit = function (item, type) {
  // 禁止选中, 避免选中影响拖拽 for firefox
  $('body').css('-moz-user-select', '-moz-none');
  // 禁止选中, 避免选中影响拖拽 for webkit & ie
  this.selectEv = document.body.onselectstart;
  document.body.onselectstart = function () { return false; }
  this.handle.css('display', 'none');
  this.handleDi.css('display', 'none');
  // focus hanle
  this.focusEl = {obj: $(item), typ: type};    // set
  // show handle
  this.calcOffsetPos().setEl();
  this.poseEl.obj.css('visibility', 'hidden'); // hidden focus
  return this.dragMove()
}

/**
 * 共有3种元素
 * 1. 焦点元素, 点击时获取到的元素
 * 2. 显示元素, 用于拖拽时显示的元素
 * 3. 占位元素, 用于拖拽到目标时占位使用 可见性为hidden
 * 拖拽点击共有2种情况
 * 1. 点击站点池的元素
 *     焦点元素为站点池元素
 *     显示元素手动创建
 *     占位元素为显示元素的克隆
 * 2. 点击地铁线站点元素
 *     焦点元素为地铁线站点元素
 *     显示元素为焦点元素的克隆
 *     占位元素就是焦点元素本身
 * 释放拖拽后
 * 1. 释放站点池元素点击
 *     需要还原焦点元素的可见性
 * 2. 释放地铁线站点元素
 *     此时焦点元素无需任何操作
 * 释放时
 *   恢复占位元素的可见性
 *   移除显示元素    
 * 
 * 站点显示与消失
 * 当显示元素拖拽/移出至目标区域
 * 将占位元素从指定区域填充/移除
 * 
 */
Drag.prototype.setEl = function () {
  if (this.focusEl.typ == 'pool') {
    this.showEl     = this.makeShowEl(this.focusEl.obj);
    this.poseEl.obj = this.showEl.clone(true);
    this.dragEnable(this.poseEl.obj, 'line');
  } else {
    this.showEl = this.focusEl.obj.clone(true);
    this.poseEl.obj = this.focusEl.obj;
    this.poseEl.dis = true;
  }
  this.showEl.css('position', 'absolute');
  $('#common-canvas').append(this.showEl);
  return this;
}

Drag.prototype.dragMove = function () {
  if (!this.focusEl.obj) return;
  this.calcMousePos();
  this.showEl.css('top',  this.mouse.y - this.offset.y + 'px');
  this.showEl.css('left', this.mouse.x - this.offset.x + 'px');
  this.setPoseEl();
  return this;
}



Drag.prototype.dragDisable = function () {
  if (!this.focusEl.obj) return this;
  if (this.focusEl.typ == 'pool') {
    this.focusEl.obj.css('visibility', '');
  }
  if (this.poseEl.dis) {
    this.poseEl.obj.css('visibility', '');
  }
  this.showEl.remove();
  // 还原可选择的状态
  $('body').css('-moz-user-select', '');
  document.body.onselectstart = this.selectEv;
  return this.initVar();
}

Drag.prototype.makeShowEl = function (item) {
  return $('<div>', {
    'class':'subline-station handle-hover', 
    'flag' :'line-station',
    'pid'  : item.attr('pid')
  }).append($('<div>', {
      'class':'station-order'
    }).append($('<span>', {
      'class':'badge badge-info'
    }).html(0))
  ).append($('<div>', {
    'class' :'station-name',
     title  : item.html(),
     text   : item.html()
  })).append($('<div>', {
    'class'     : 'direction',
    'direction' : '0',
  }).append($('<i>', {
    'class' : 'icon-resize-vertical'
  })));
}

Drag.prototype.setPoseEl = function () {
  var sepPosX = $('#subline-station-box').positions().left;
  if (this.poseEl.dis == true && this.mouse.x < sepPosX) {
    var _this = this;
    // no ajax
    // none
    
    // try ajax
    /*
    $.ajax({
      type: "POST",
      url: "/ajax/mongo/removeStationFromSubline",
      async : false,
      dataType: 'json',
      data: {data:{
        lineId    : $('#lineId').val(),
        sublineId : $('#subline-station-box .active').attr('id').substr(8),
        poiId     : _this.poseEl.obj.attr('pid'),
        order     : _this.poseEl.obj.find('.badge').html()
      }},
      success: function(data) {
        if (data.code != 100) return alert(data.errorMsg + "\n删除失败, 请联系管理员");
      },
      error: function() {
        alert("Ajax Error\n删除失败, 请联系管理员");
      }
    });
    */
    this.poseEl.obj.removeSelf();
    this.poseEl.dis = false;
    this.calcLineOrder();
    
  }
  if (this.poseEl.dis == false && this.mouse.x > sepPosX) {
    this.insertPoseEl();
    this.poseEl.dis = true;
  }
  if (this.poseEl.dis) {
    this.orderPoseEl();
  }
}

Drag.prototype.insertPoseEl = function () {
  var _this = this;
  $(this.target).each(function(i, el){
    var pos = $(el).positions();
    if (pos.top > _this.mouse.y) {
      $(_this.lineBox).insBefore(_this.poseEl.obj, el);
      _this.poseEl.dis = true;
      return false;
    }
  });
  if (!this.poseEl.dis || $(this.target).length == 0) {
    $(this.lineBox).appendChild(this.poseEl.obj);
  }
}

Drag.prototype.orderPoseEl = function () {
  var _this = this;
  var is_before = true;
  $(this.target).each(function(i, el){
    var pos = $(el).positions();
    if (_this.poseEl.obj.get(0) == el) {
      is_before = false;
      return true; // continue
    } 
    var top = pos.top + $(el).height() / 2;
    if ((is_before && top > _this.mouse.y) ||
        (!is_before && top < _this.mouse.y)) {
      $(_this.lineBox).exchange(_this.poseEl.obj, el);
      return false; // break
    }
  });
  this.calcLineOrder();
}

Drag.prototype.calcLineOrder = function () {
  var _this = this;
  $(this.target).each(function(i, el){
    $(el).find('.badge').html(i+1);
    if (_this.poseEl.obj && el == _this.poseEl.obj.get(0)) {
      _this.showEl.find('.badge').html(i+1);
    }
  });
}

Drag.prototype.calcMousePos = function() {
  if(this.ev.pageX || this.ev.pageY){
    this.mouse = {x: this.ev.pageX, y: this.ev.pageY};
  } else {
    var bodyPos = $(document.body).position();
    this.mouse = {
//      x: this.ev.clientX + bodyPos.left, 
//      y: this.ev.clientY + bodyPos.top
        x: this.ev.clientX + document.body.scrollLeft - document.body.clientLeft,
        y: this.ev.clientY + document.body.scrollTop  - document.body.clientTop
    };
  }
  return this;
}

Drag.prototype.calcOffsetPos = function() {
  this.calcMousePos();
  var focusPos = this.focusEl.obj.positions(this.focusEl.obj);
  this.offset = {
    x: this.mouse.x - focusPos.left,
    y: this.mouse.y - focusPos.top
  };
  return this;
}



//  return getNew('div').attr('class', 'subline-station').append(
//            getNew('div').attr('class', 'station-order').append(
//              getNew('span').attr('class', 'badge badge-info').html(0)
//            )
//          ).append(
//            getNew('div').attr('class', 'station-name').html(name)
//          );
