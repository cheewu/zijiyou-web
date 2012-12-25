<?php include 'header.php'; ?>

<div class="regist_usering">
	<form action="" method="post">
		<span>邮箱：</span>
		<input name="login_cl2" type="text" class="login_cl" id="login_cl2" />
		<span>密码：</span>
		<input name="login_cl" type="password" class="login_cl" id="login_cl" />
		<span>名号：</span>
		<input name="login_cl" type="text" class="login_cl" id="login_cl" />
		<div class="regist_loginal"><input name="" type="image" src="/images/sep/zhuce.jpg" /></div>
		<div class="regist_agreed">点击注册即表示已阅读并同意《自己游用户使用协议》</div>
	</form>
</div>
<div class="regist_plate">
	<h1>或通过站外账号进行登录：</h1>
	<div class="regist_img"><a href="#"><img src="/images/sep/c_renren.gif" width="95" height="23" border="0" /></a></div>
	<div class="regist_img"><a href="#"><img src="/images/sep/c_qq.gif" width="95" height="26" border="0" /></a></div>
	<div class="regist_img"><a href="<?=$oauth_url?>"><img src="/images/sep/c_sina.gif" width="95" height="25" border="0" /></a></div>
	<div class="regist_img"><a href="#"><img src="/images/sep/c_msn.gif" width="95" height="23" border="0" /></a></div>
	<div class="regist_img"><a href="#"><img src="/images/sep/c_kaixin.jpg" width="94" height="23" border="0" /></a></div>
	<div class="regist_img"><a href="#"><img src="/images/sep/c_sohu.gif" width="95" height="26" border="0" /></a></div>
</div>

<?php include 'footer.php'; ?>