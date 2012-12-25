<?php  include 'header.php'; ?>
<script type="text/javascript">
function itemDelete(type, id) {
	$.ajax({
    	type: "GET",
    	url: "/subway/delete/" + id,
    	dataType: 'html',
    	success: function(sig) {
  			if(sig == '1') {
  				$('#'+id).fadeOut();
    		}
  		}
  	});
}
function addNewLine() {
  var region = $.trim($('#q').val());
  if (region == '') {
    alert('地域不能为空'); return;
  }
  window.location = '/subway/add_line/?region=' + region;
}
</script>
<div class="lists_wrapper">
	<div class="search_box">
	<form id="search_box" method="get" action="/subway">
	    <span>地域:</span>
		<input type="text" id="q" name="q" value="<?=$this->q?>" />
		<input type="button" value="搜索" onclick="$('#search_box').submit()" />
		<input type="button" value="添加" onclick="addNewLine();" />
	</form>
</div>
<?php 
	$link = tpl_link_generater('/subway/', array('pg' => null));
	$curpage = $this->input->get('pg') ? $this->input->get('pg') : 1;
	$multi = multi($this->count, $this->ps, $curpage, $link);
	echo <<<HTML
<div class="page">
	$multi
</div>
HTML;
?>
	<table>
		<tr class="headline">
<?php 
    $fields[] = 'edit';
    $fields[] = 'delete';
	foreach($fields AS $index => $name) {
		$width = (100 / count($fields)).'%';
		echo <<<HTML
			<td width="$width">$name</td>
HTML;
	}
	echo '
		</tr>';
	foreach($lists AS $key => $value) {
	    $id = strval($value['_id']);
		echo <<<HTML
		<tr id="{$id}">
HTML;
		foreach($fields AS $index) {
			$link = '';
			$content = isset($value[$index]) ? $value[$index] : '';
		    if($index == 'stationList') {
		        $all_station = array();
			    foreach ($value[$index] AS $station) {
		            $all_station[] = $station['stationName'];
			    }
			    $content = implode(" ", $all_station);  
			}
			$content = utf8_str_cut_off($content, 10);
		    if($index == 'wiki') {
		        $content = <<<HTML
		        	<a href="{$value[$index]}" target="_blank">维基百科</a>
HTML;
			}
			if($index == 'edit') {
		        $content = <<<HTML
		        	<a href="/subway/item/$id" target="_blank">编辑</a>
HTML;
			} 
		    if($index == 'delete') {
		        $content = <<<HTML
		        	<a href="javascript::void(0);" onclick="itemDelete('subway', '{$value['_id']}');">删除</a>
HTML;
			} 
?>
			<td><?=$content?></td>
	<?php } ?>
		</tr>
<?php } ?>
	</table>
</div>
<?php include 'footer.php';?>