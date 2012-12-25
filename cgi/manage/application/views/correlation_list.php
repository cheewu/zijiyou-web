<?php  include 'header.php'; ?>
<script type="text/javascript">
function itemDelete(type, id) {
	$.ajax({
    	type: "GET",
    	url: "/ajax/delete/" + type + "/" + id,
    	dataType: 'html',
    	success: function(sig) {
  			if(sig == '1') {
  				$('#'+id).fadeOut();
    		}
  		}
  	});
}
</script>
<?php 
$q = $this->input->get("q") ? $this->input->get("q") : "";

?>
<div class="lists_wrapper">
	<div class="search_box">
	<form id="search_box" method="get" action="/correlation">
		<input type="text" id="q" name="q" value="<?=$q?>" />
		<input type="button" value="搜索" onclick="$('#search_box').submit()" />
	</form>
</div>
<?php 
	$link = tpl_link_generater('/correlation/', array('pg' => null));
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
	array_push($fields, "edit");
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
			$content = utf8_str_cut_off($content, 10);
			if($index == 'edit') {
		        $content = <<<HTML
		        	<a href="/correlation/item/$id" target="_blank">编辑</a>
HTML;
			} 
		    if($index == 'delete') {
		        $content = <<<HTML
		        	<a href="javascript::void(0);" onclick="itemDelete('correlation', '{$value['_id']}');">删除</a>
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