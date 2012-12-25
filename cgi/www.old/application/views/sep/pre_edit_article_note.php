<?php include 'pre_edit_header.php';?>

<div class="lv_title"><?=$result['title']?></div>
<div class="lv_center">
	<h1>详情</h1>
		<ol>
			<dt>名称</dt>
			<dd>
				<input name="phone" type="text" class="lv_field" value="<?=$result['title']?>" />
			</dd>
		</ol>
		<ol>
			<dt>描述</dt>
	    	<dd>
				<textarea name="textarea" class="article_content">
					<?=$result['content']?>
				</textarea>
		</dd>
	</ol>
</div>
<div class="button_sc">
	<label>
		<img id="submit_button" class="button" src="/images/sep/sc.jpg" />
		<a href="<?=$coming_url?>"><img class="button" src="/images/sep/return.jpg" /></a>
	</label>
</div>

<?php include 'pre_edit_footer.php';?>