<?php
class Poi extends base_detail {
	public function detail() {
		$param_append = array(
			'sub_select_arr' => array(
				'category' => $this->mongo_db->POI_distinct_by_category(),
			),
		);
		parent::detail(array(), $param_append, false);
		!empty($this->detail['regionId']) && $region = $this->mongo_db->Region_fetch_by__id($this->detail['regionId']);
		!empty($region['name']) && $this->detail['area'] = $region['name'];
		$this->base_view();
	}
}
	
