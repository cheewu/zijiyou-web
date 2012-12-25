<?php  include 'header.php'; ?>
<?php tpl_nav()?>
<?php tpl_add_botton()?>
<?php tpl_multi_page()?>

<?php tpl_search_box('/search/lists')?>
<?php tpl_table_lists($lists, $fields)?>
<?php include 'footer.php';?>
