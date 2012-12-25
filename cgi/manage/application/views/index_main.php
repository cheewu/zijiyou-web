<?php  include 'header.php'; ?>

<?php 
$ci = &get_instance();
	$config = $ci->config->item('search');
	$category = $config['category'];
	echo <<<HTML
<div class="index">
	<ul>
HTML;
	foreach($category AS $value) {
		echo <<<HTML
		<a href="/search/lists/?collection=$value" target="_blank">
			<li>$value</li>
		</a>
HTML;
	}
	echo '
		<a href="/correlation" target="_blank">
			<li>correlation</li>
		</a>
		<a href="/subway" target="_blank">
			<li>subway</li>
		</a>
		<a href="/images" target="_blank">
			<li>images</li>
		</a>
	</ul>
</div>'; 

?>

<?php include 'footer.php';?>