<?php
class Base_detail extends CI_Controller {
	
	public $query = array();
	
	public $conf = array();
	
	public $detail = array();
	
	public $param_view = array();
	
	public $collection;
	
	public function __construct() {
		parent::__construct();
		$this->query = $this->input->get();
		$this->collection = get_class($this);
		$this->collection == 'Poi' && $this->collection = 'POI';
		$this->conf = $this->config->item(strtolower($this->collection).'_field');
	}
	
	public function index() {
		$this->load->view('detail');
	}
	
	public function detail($param, $param_append, $is_view = true) {
		$mongo_func = $this->collection.'_fetch_by_';
		$mongo_func .= !empty($param['mongo_fetch_field']) ? $param['mongo_fetch_field'] : '_id';
		//!empty($param['mongo_func']) && $mongo_func = $param['mongo_func'];
		$detail = $this->mongo_db->$mongo_func($this->query['_id']);
		$this->detail = array_merge($detail, $this->detail);
		$this->title = $this->collection.' Detail';
		$this->param_view = $param_append;
		$is_view && $this->base_view();
	}
	
	public function base_view() {
		$param = array(
			'text_arr' => $this->conf['text'],
			'select_arr' => $this->conf['option'],
			'textarea_arr' => $this->conf['textarea'],
			'action' => '/post/'.$this->collection,
			'center' => $this->detail['center'],
			'detail' => $this->detail,	
			'collection' => $this->collection,
		);
		$this->param_view = array_merge($param, $this->param_view);
		$this->load->view('detail', $this->param_view);
	}
	
	public function add() {
		$this->param_view = array(
			'disable_option' => array(
				'add_item' => true,
			),
		);
		$this->base_view();
	}
}