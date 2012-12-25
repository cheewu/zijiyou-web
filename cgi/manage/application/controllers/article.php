<?php 
class Article extends base_detail {
	
	public function detail() {
		$param['sub_select_arr']['is_recommend'] = array(0, 1);
		$param_append = array(
			'disable_option' => array(
				'map' => true,
				'upload_img' => true,
			),
		);
		parent::detail($param, $param_append);
	}
}
