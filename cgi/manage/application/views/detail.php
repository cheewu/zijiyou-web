<?php include 'header.php'; ?>
<div> <?=$top_nav?> </div>
<form id="detail_all" method="post" action="<?=$action?>">
<?php 
!isset($disable_option['add_item']) && tpl_add_id($detail);
unset($text_arr['center']);
!isset($disable_option['text']) && tpl_input_text($text_arr, $detail);
!isset($disable_option['select']) && tpl_select($select_arr, $detail, $sub_select_arr) ;
!isset($disable_option['textarea']) && tpl_textarea($textarea_arr, $detail);
!isset($disable_option['map']) && tpl_map($center, $detail);
!isset($disable_option['upload_img']) && tpl_upload_img($detail['_id'], $collection, $detail);
?>
</form>
<input id="main_submit" type="button" value="提交" onclick="javascript:$('#detail_all').submit()"/>
<?php include 'footer.php'; ?>