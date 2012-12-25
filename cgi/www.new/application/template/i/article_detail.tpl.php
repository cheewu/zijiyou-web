<?php include 'header.tpl.php'; ?>

<div id="wrap" class="article-detail">
  <div class="headline">
    <h1 class="font-dt-hl"><?=$article['title']?></h1>
    <?php if (isset($article['optDateTime'])) {?>
    <i class="clock"></i>
    <span class="font-dt-hl-date">
      <?=date('Y-m-d H:i:s', $article['optDateTime']->sec)?>
    </span>
    <?php }?>
  </div>
  <div class="content font-dt-ct">
<?php 
  foreach ($paragraph_arr AS $paragraph) {
    list($prg_id, $prg_str) = $paragraph;
    if (substr($prg_str, 0, 4) == '<img') {
      echo <<<HTML
    <div class="imgbox box-shadow">
      $prg_str
    </div>
HTML;
    } else {
      $prg_str = strip_tags($prg_str);
      echo <<<HTML
    <p>$prg_str</p>
HTML;
    }
  }
?>
  </div>
</div>

<?php include 'footer.tpl.php';?>