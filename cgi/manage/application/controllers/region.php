<?php

class Region extends base_detail {
	
	public function detail() {
		$param_append = array(
			'sub_select_arr' => array(
				'category' => $this->mongo_db->Region_distinct_by_category(),
			),
		);
		$this->detail['poi_cnt'] = $this->mongo_db->mongodb->POI->find(array('regionId' => new MongoID($this->query['_id'])))->count();
		parent::detail(array(), $param_append);
	}
}














