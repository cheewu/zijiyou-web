<?php
class Wikipedia extends base_detail {
	
	public function detail() {
		$param['mongo_fetch_field'] = '_id_noo';
		$param_append = array(
			'disable_option' => array(
				'select' => true,
				'map' => true,
				'upload_img' => true,
			),
		);
		parent::detail($param, $param_append);
	}
}