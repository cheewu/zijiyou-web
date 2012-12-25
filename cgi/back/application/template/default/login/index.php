<div id="common-canvas" class="login">
  <div class="container">
    <form class="form-signin" action="/login/auth/?refer=<?=$this['refer']?>" method="post">
      <h2 class="form-signin-heading">Please sign in</h2>
      <input name="email" type="text" class="input-block-level" placeholder="Email address">
      <input name="password" type="password" class="input-block-level" placeholder="Password">
      <?php if (!empty($this['auth_fail_alert'])): ?>
      <div class="alert alert-error">用户名或密码错误</div>
      <?php endif; ?>
      <label class="checkbox">
        <?php $checked = (!empty($this['remember_me']) ? 'checked' : ''); ?>
        <input name="remember_me" type="checkbox" value="remember-me" <?=$checked?> /> Remember me
      </label>
      <button class="btn btn-large btn-primary" type="submit">Sign in</button>
    </form>
  </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
  verticalMiddle();
  $(window).resize(verticalMiddle);
});
function verticalMiddle() {
  var height = $(window).height() - $('#common-canvas').height();
  $('#common-canvas').css('padding-top', parseInt(height * 0.4));
}
</script>