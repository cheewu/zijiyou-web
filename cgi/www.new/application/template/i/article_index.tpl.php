<?php include 'header.tpl.php'; ?>
<script type="text/javascript">
var id = '<?=strval($_GET['id'])?>';
var pg = <?=$pg?>;
</script>
<div id="wrap" class="article-list">
<?php 
foreach($documents AS $article_index => $article) {
  $r_id = strval($region['_id']);
  $a_id = strval($article['_id']);
  echo <<<HTML
  <div class="item" onclick="go('/detail/$r_id/$a_id?mobile=1')">
HTML;
  $image = tpl_mobile_article_has_image($article['pictures']);
  if ($image !== false) {
    echo <<<HTML
    <div class="img">
      <img src="$image"/>
    </div>
HTML;
  }
  $content_class = ($image === false) ? 'content-all' : 'content';
  $title = @$article['title'] ?: $region['name']."游记";
  $content = tpl_mobile_article_summary($article['content'], $image);
  echo <<<HTML
    <div class="$content_class">
      <h1 class="font-li-hl">$title</h1>
      $content
    </div>
  </div>
  <hr />
HTML;
}
?>
</div>
<!--<h1 id="debug" style="font-size:50px;color:red;position:absolute;left:10px;"></h1>-->
<script type="text/javascript">
/**
 * Global vars
 */
var is_loading = false;
if (navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
  setInterval(function(){
    var totalTop = $(document.body).height() - $(window).height();
    //$('#debug').html(totalTop - window.pageYOffset).css('top', window.pageYOffset + 10);
    if (totalTop - window.pageYOffset < $(window).height() * 2) {
      scrollLoad();
    }
  }, 20);
} else {
  $(window).scroll(function(){
    var totalTop = $(document.body).height() - $(window).height();
    if (totalTop - $(document).scrollTop() < $(window).height() * 2) {
      scrollLoad();
    }
  });
}

function scrollLoad()
{
  if (is_loading == true) return;
  is_loading = true;
  $.ajax({
    type: "GET",
    url: "./" + id + "/?page_down=1&pg=" + (pg+1),
    async : true,
    dataType: 'json',
    error: function() { is_loading = false; },
    success: function(data) {
      if (data.length <= 0) {
        setTimeout(function(){
          is_loading = false;
        }, 5000); return;
      }
      $.each(data, function(k, v) {
        $('#wrap').append(getListItem(v)).append('<hr />');
      });
      // 请求成功 页码+1
      pg += 1;
      setTimeout(function(){
        is_loading = false;
      }, 1000);
    },
  });
}

/**
 * generate list item
 * @param json item
 * @return object
 */
function getListItem(item) {
  if (!item) return null;
  var title = document.createElement('h1');
  $(title).addClass('font-li-hl').html(item.title);
  //var shortcut = document.createElement('p');
  //$(shortcut).addClass('font-li-ct').html(item.shortcut);
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
  $(content_div).append(title).append(item.shortcut);
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
<?php include 'footer.tpl.php';?>