<?php 
header('Content-Type: text/html; charset=utf-8');
?>
<html>
<head>
<meta name="viewport" content="width=640">
<link href="./style/main.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/application/template/i/javascript/jquery-1.7.2.min.js"></script>
</head>
<body>
<div id="wrap" class="article-list">
  <div class="item" onclick="go('./a.php')">
    <div class="img">
      <img src="./images/attr.png" width="120px"/>
    </div>
    <div class="content">
      <h1 class="font-li-hl">巴黎，享受自由的行走</h1>
      <p class="font-li-ct">虽然读书的时候学过一段时间法语，但对于法国的体验，是从登上法航的航班开始的。</p>
    </div>
  </div>
  <hr />
  <div class="item" onclick="go('./a.php')">
    <div class="img">
      <img src="./images/attr.png" width="120px"/>
    </div>
    <div class="content">
      <h1 class="font-li-hl">巴黎，享受自由的行走</h1>
      <p class="font-li-ct">虽然读书的时候学过一段时间法语，但对于法国的体验，是从登上法航的航班开始的。</p>
    </div>
  </div>
  <hr />
  <div class="item" onclick="go('./a.php')">
    <div class="img">
      <img src="./images/attr.png"/>
    </div>
    <div class="content">
      <h1 class="font-li-hl">巴黎，享受自由的行走</h1>
      <p class="font-li-ct">虽然读书的时候学过一段时间法语，但对于法国的体验，是从登上法航的航班开始的。</p>
    </div>
  </div>
  <hr />
  <div class="item" onclick="go('./a.php')">
    <div class="content-all">
      <h1 class="font-li-hl">巴黎，享受自由的行走</h1>
      <p class="font-li-ct">虽然读书的时候学过一段时间法语，但对于法国的体验，是从登上法航的航班开始的。虽然读书的时候学过一段时间法语，但对于法国的体验，是从登上法航的航班开始的。</p>
    </div>
  </div>
  <hr />
  <div class="item" onclick="go('./a.php')">
    <div class="img">
      <img src="./images/attr.png" width="100px"/>
    </div>
    <div class="content">
      <h1 class="font-li-hl">巴黎，享受自由的行走</h1>
      <p class="font-li-ct">虽然读书的时候学过一段时间法语，但对于法国的体验，是从登上法航的航班开始的。</p>
    </div>
  </div>
  <hr />
  <div class="item" onclick="go('./a.php')">
    <div class="img">
      <img src="./images/attr.png" width="130px" height="100px"/>
    </div>
    <div class="content">
      <h1 class="font-li-hl">巴黎，享受自由的行走</h1>
      <p class="font-li-ct">虽然读书的时候学过一段时间法语，但对于法国的体验，是从登上法航的航班开始的。</p>
    </div>
  </div>
  <hr />
  <div class="item" onclick="go('./a.php')">
    <div class="content-all">
      <h1 class="font-li-hl">巴黎，享受自由的行走</h1>
      <p class="font-li-ct">虽然读书的时候学过一段时间法语，但对于法国的体验，是从登上法航的航班开始的。虽然读书的时候学过一段时间法语，但对于法国的体验，是从登上法航的航班开始的。</p>
    </div>
  </div>
  <hr />
</div>
<div id="scroll-top" style="position:fixed; left: 5px; top: 5px; background-color: #3f3f3f; color:white;"></div>
<script type="text/javascript">
/*
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
*/

/**
 * Global vars
 */
var is_loading = false;
$(window).scroll(function(){
  var scroll_top = $(document.body).scrollTop();
  var bottom_scroll_top = $(document.body).height() - $(window).height() - 5;
  
  $('#scroll-top').html(bottom_scroll_top + " " + scroll_top);
  
  if (scroll_top < bottom_scroll_top) return;
  if (scroll_top >= bottom_scroll_top) {
    if (is_loading == true) return;
  }
  is_loading = true;
  $.ajax({
    type: "GET",
    url: "/i/ajax.php",
    async : false,
    dataType: 'text',
    error: function() { is_loading = false; },
    //data: insert,
    success: function(data) {
      try {
        eval('var list=' + data + ';');
      } catch (e) {
        setTimeout('is_loading = false;', 1000); return;
      }
      if ($(list).size() <= 0) {
        setTimeout('is_loading = false;', 5000); return;
      }
      $.each(list, function(k, v) {
        $('#wrap').append(getListItem(v)).append('<hr />');
      });
      setTimeout('is_loading = false;', 1000);
    }
  });
  
});

/*
<div class="item" onclick="go('./a.php')">
  <div class="img">
    <img src="./images/attr.png" width="120px"/>
  </div>
  <div class="content">
    <h1 class="font-li-hl">巴黎，享受自由的行走</h1>
    <p class="font-li-ct">虽然读书的时候学过一段时间法语，但对于法国的体验，是从登上法航的航班开始的。</p>
  </div>
</div>
 */
/**
 * generate list item
 * @param json item
 * @return object
 */
function getListItem(item) {
  if (!item) return null;
  var title = document.createElement('h1');
  $(title).addClass('font-li-hl').html(item.title);
  var shortcut = document.createElement('p');
  $(shortcut).addClass('font-li-ct').html(item.shortcut);
  var image_div = '';
  var content_div = document.createElement('div');
  if (item.image) {
    var image = document.createElement('img');
    $(image).attr('src', item.image);
    var image_div = document.createElement('div');
    $(image_div).addClass('img').append(image);
    $(content_div).addClass('content');
  } else {
    $(content_div).addClass('content-all');
  }
  $(content_div).append(title).append(shortcut);
  var out_div = document.createElement('div');
  $(out_div).addClass('item')
            .attr('onclick', 'go(\'' + item.href + '\')')
            .append(image_div)
            .append(content_div);
  return out_div;
}

/**
 * goto specific url
 * @param url
 */
function go(url) {
  location.assign(url);
}
</script>
</body>
</html>