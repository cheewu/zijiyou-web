<?php include 'header.tpl.php'; ?>
<div id="middle">
	<div class="classify">
		<?=crumbs($region_id, "", "文章")?>
	</div>

	<div id="center">
		<div class="writings">
			<h1><?=$fragement['title']?></h1>
			<div class="writings_text">
			    <?=isset($fragement['optDateTime']) ? date('Y-m-d H:i:s', $fragement['optDateTime']->sec) : ""?><?php // 　　<a href="#"><?=@$fragement['author'] ?: ""? ></a> 写了 <a href="#"><?=$region['name']? >游记攻略</a> 11感谢　　40收藏　　45评论 ?></div>
			<?=$seelcted_fragement?>
		</div>
		<div class="right_return">
		<style>#center .right_return h1{float:none;text-align:right;}</style>
			<h1><a href="/detail/<?=$region_id?>/<?=$fragement['documentID']?>">&gt;查看游记全文</a></h1>
			<h1 style="margin-top:10px;"><a href="<?=@$_SERVER['HTTP_REFERER'] ?: '#'?>">&gt;返回到<?=$region['name']?>攻略</a></h1>
		</div>
	</div>
</div>



<?php include 'footer.tpl.php'; ?>