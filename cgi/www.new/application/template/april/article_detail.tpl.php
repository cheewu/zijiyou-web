<?php include 'header.tpl.php'; ?>
<div id="middle">
	<div class="classify">
		<?=crumbs($region_id, "", "文章")?>
	</div>

	<div id="center">
		<div class="writings">
			<h1><?=$article['title']?></h1>
			<div class="writings_text"><?=isset($article['optDateTime']) ? date('Y-m-d H:i:s', $article['optDateTime']->sec) : ""?><?php // 　　<a href="#"><?=@$article['author'] ?: ""? ></a> 写了 <a href="#"><?=$region['name']? >游记攻略</a> 11感谢　　40收藏　　45评论 ?></div>
<?php 
            foreach ($paragraph_arr AS $paragraph) {
              list($prg_id, $prg_str) = $paragraph;
              $prg_str = nl2br($prg_str);
              echo <<<HTML
              <p p-id="$prg_id">$prg_str</p>
HTML;
            }
?>	
		</div>
		<div class="right_return">
			<h1><a href="<?=@$_SERVER['HTTP_REFERER'] ?: '#'?>">&gt;返回到<?=$region['name']?>攻略</a></h1>
		</div>
	</div>
</div>



<?php include 'footer.tpl.php'; ?>