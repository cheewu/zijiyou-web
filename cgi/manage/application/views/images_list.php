<?php  include 'header.php'; ?>
<!--fancybox-->
<!-- Add mousewheel plugin (this is optional) -->
<script type="text/javascript" src="/fancybox/lib/jquery.mousewheel-3.0.6.pack.js"></script>

<!-- Add fancyBox -->
<link rel="stylesheet" href="/fancybox/source/jquery.fancybox.css?v=2.1.0" type="text/css" media="screen" />
<script type="text/javascript" src="/fancybox/source/jquery.fancybox.pack.js?v=2.1.0"></script>

<!-- Optionally add helpers - button, thumbnail and/or media -->
<link rel="stylesheet" href="/fancybox/source/helpers/jquery.fancybox-buttons.css?v=1.0.3" type="text/css" media="screen" />
<script type="text/javascript" src="/fancybox/source/helpers/jquery.fancybox-buttons.js?v=1.0.3"></script>
<script type="text/javascript" src="/fancybox/source/helpers/jquery.fancybox-media.js?v=1.0.3"></script>

<link rel="stylesheet" href="/fancybox/source/helpers/jquery.fancybox-thumbs.css?v=1.0.6" type="text/css" media="screen" />
<script type="text/javascript" src="/fancybox/source/helpers/jquery.fancybox-thumbs.js?v=1.0.6"></script>
<!--end fancybox-->
<style type="text/css">
<!--
.images-name {
  display:inline-block;
}

.images-name div {
  margin:0 auto;  
  width:70px;
  height:150px;  
   
  display:table-cell;
  vertical-align:middle;
  text-align:center;
  /* ie6 hack */
  #position:relative;
}

.images-name span {
  /* ie6 hack */
  #position:absolute;
  #top:50%; 
}

.img-box { 
  display:inline-block;
  position:relative;
}

.img-box img {
  width:150px;
  height:150px;
}

.img-handle {
  width:100%;
  position:absolute;
  text-align:center;
  background-color:rgba(152,152,152,0.5);
  cursor:default;
}

.img-handle-edit, .img-handle-delete {
  text-decoraction:none;
  cursor:pointer;
}

.img-handle-edit {
  float:left;
  padding-left: 30px;
}

.img-handle-delete {
  float:right;
  padding-right: 30px;	
}

-->
</style>
<div class="nav">
  <div class="_nav_box">
	<?php 
	foreach($this->nav AS $cate => $selected) {
      $selected_class = $selected ? '_nav_selected' : '';
      $link = $selected ? '#' : tpl_link_generater("/images/{$this->collection}/", array('category' => $cate));
      echo <<<HTML
    	<a href="$link" class="_nav_item $selected_class">$cate</a>
HTML;
	}
?>
  </div>
</div>
<hr />
<input type="hidden" id="type" value="<?=$this->collection?>"/>
<div class="lists_wrapper">
  <div class="search_box">
    <form id="search_box" method="get" action="/images/<?=strtolower($this->collection)?>">
      <input type="text" id="q" name="q" value="<?=$this->q?>" />
      <input type="button" value="搜索" onclick="$('#search_box').submit()" />
    </form>
  </div>
<?php 
	$link = tpl_link_generater("/images/{$this->collection}", array('pg' => null));
	$curpage = $this->input->get('pg') ? $this->input->get('pg') : 1;
	$multi = multi($this->count, $this->ps, $curpage, $link);
	$cur_url = tpl_link_generater("/images/{$this->collection}");
	echo <<<HTML
  <div class="page">$multi</div>
HTML;
foreach ($list AS $item) {
  $mongoId = strval($item['_id']);
  $collection = strtolower($this->collection);
  echo <<<HTML
  <div id="$mongoId" class="images-line">
    <div class="images-name">
      <div>
        <span>
          <a href="http://manage.zijiyou.com/$collection/detail?_id=$mongoId" target="_blank">{$item['name']}</a>
        </span>
        <input mongoId="$mongoId" class="img-add" type="button" value="添加图片"/>
      </div>
    </div>
HTML;
  foreach ($item['googleImages'] AS $images) {
    if (!isset($images['up_url'])) continue;
    echo <<<HTML
    <div class="img-box" imageId="{$images['imageId']}" mongoId="$mongoId">
      <div class="img-handle" style="display:none;">
        <a class="img-handle-edit"><i class="icon-pencil"></i></a>
        <a class="img-handle-delete"><i class="icon-remove-sign"></i></a>
      </div>
      <a href="{$images['up_url']}" target="_blank"><img src="{$images['up_url']}150x150" /></a>
    </div>
HTML;
  }
  echo "</div>";
}
?>
</div>
<script type="text/javascript">
$(document).ready(function(){
  imageHandle();
});
function imageHandle() {
  $('.img-box').hover(
    function () {
      $(this).children('.img-handle').css('display', '');
    },
    function () {
      $(this).children('.img-handle').css('display', 'none');
    }
  );
  $('.img-handle-delete').click(function(){
    var parent = this.parentNode.parentNode;
    var id = $(parent).attr('imageId');
    var mongoId = $(parent).attr('mongoId');
    var type = $('#type').val();
    var flag = false;
    $.ajax({
      type: "GET",
      url: "/images/move_to_trash/" + type + '/' + mongoId + '/' + id,
      async : false,
      dataType: 'json',
      success: function(sig) {
        if (sig.response_code == '200') {
          alert('[错误]:' + sig.response_msg); return;
        }
        flag = true
      }
    });
    if (!flag) return;
    $(parent).fadeOut(function(){
      var line = parent.parentNode;
      parent.parentNode.removeChild(parent);
      var imgs = $(line).children('.img-box');
      if (imgs.length > 0) return;
      //line.parentNode.removeChild(line);
    });
  });
}
$('.img-add').click(function(){
  var mongoId = $(this).attr('mongoId');
  var type = $('#type').val();
  $.fancybox.open({
		href    : '/images/upload/' + type + '/' + mongoId,
		type    : 'iframe',
		padding : 0,
		margin  : 0,
		width   : 500
	});
});
</script>
<?php include 'footer.php';?>