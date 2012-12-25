<div id="common-header">
  <div class="navbar navbar-inverse" style="position: static;">
    <div class="navbar-inner">
      <div class="container">
        <a class="brand" href="/">Edit</a>
        <div class="nav-collapse collapse navbar-inverse-collapse">
          <ul class="nav">
<?php
$nav_name = strtolower(Mf::$url['controller']);
include  'nav/' . $nav_name . '.php';
?>
          </ul>
          <ul class="nav pull-right">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <?php 
                $email = Mf::$global['user']['email'];
                echo current(explode("@", $email, 2));
                ?>
                <b class="caret"></b>
              </a>
              <ul class="dropdown-menu">
<!--                <li><a href="#">Action</a></li>-->
<!--                <li><a href="#">Another action</a></li>-->
<!--                <li><a href="#">Something else here</a></li>-->
<!--                <li class="divider"></li>-->
                <?php $refer = rawurlencode($this->safeGet('refer', '/'));?>
                <li><a href="/login/clear/?refer=<?=$refer?>">logout</a></li>
              </ul>
            </li>
          </ul>
<?php 
if (strtolower(Mf::$url['controller']) != 'edit') {
  $qf = Mf::$input->safeGet('qf', 'name');
?>
          <form class="navbar-search pull-right">
            <input name="q" type="text" class="search-query span2" placeholder="Search" value="<?=$this->safeGet('q', '')?>" />
            <input id="search-field" name="qf" type="hidden" value="<?=$qf?>" />
          </form>
          <ul class="nav pull-right">
<?php 
if (strtolower(Mf::$url['action']) != 'subway') {
  $select = Mf::$input->safeGet('ct', '');
  $category = Mf::$mongo['tripfm']->command(
    array("distinct" => $this['col'], "key" => 'category')
  );
?>
             <li class="dropdown">
               <a href="#" class="dropdown-toggle" data-toggle="dropdown">Category <b class="caret"></b></a>
               <ul class="dropdown-menu">
<?php
  foreach ($category['values'] as $ct) {
    echo sprintf('<li class="%s"><a href="%s">%s</a></li>',
      $select == $ct ? 'active' : '',
      $select == $ct ? Mf::$url->makeOrigin(array('ct' => null, 'pg' => null), MfUrl::Q_MERGE) : 
                       Mf::$url->makeOrigin(array('ct' => $ct, 'pg' => null), MfUrl::Q_MERGE),
      $ct
    );
  }
  if (strtolower(Mf::$url['action']) == 'region') {
?>
              <li class="divider"></li>
<?php
    $is_important = Mf::$input->safeGet('ipt', 0);
    $url = Mf::$url->makeOrigin(array('ipt' => !$is_important, 'pg' => null), MfUrl::Q_MERGE);
    echo sprintf('<li class="%s"><a href="%s">is_important</a></li>',
      $is_important ? 'active' : '',
      $url
    ); 
  }
  
?>
              </ul>
            </li>
<?php 
}
?>
            <li><a id="nav-search-field" href="javascript:void(0);"><?=ucfirst($qf)?></a></li>
          </ul>
<?php 
}
?>
        </div><!-- /.nav-collapse -->
      </div>
    </div><!-- /navbar-inner -->
  </div>
</div>
<script type="text/javascript">
$('#nav-search-field').click(function(){
  var input = $('#search-field');
  var label = $('#nav-search-field')
  var qf = input.val();
  if (qf == 'name') {
    input.val('area');
    label.html('Area');
  } else {
    input.val('name');
    label.html('Name');
  }
});
</script>
