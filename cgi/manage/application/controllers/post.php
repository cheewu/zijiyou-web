<?php
class Post extends CI_Controller {
	
	public $conf;
	
	public $collection;
	
	public function __construct() {
		parent::__construct();
		$this->query = $this->input->get();
	}
	
	public function pic_upload_iframe() {
		$this->load->view('pic_upload');
	}
	
	public function pic_upload() {
		$collection = $_POST['collection'];
		$_id = $_POST['_id'];
		$file = $_FILES['pic_container'];
		if(!empty($file['name'])) {
			preg_match("/[^\.]+$/", $file['name'], $match);
			$suffix = $match[0];
			$version = 0;
			while(1) {
				$img_path = M_ROOT.'tmp/'.$collection.'_'.$_id;
				$img_url = '/tmp/'.$collection.'_'.$_id;
				if($version) { 
					$img_path .= '_'.$version;
					$img_url .= '_'.$version;
				}
				$img_path .= '.'.$suffix;
				$img_url .= '.'.$suffix;
				$version ++ ;
				if(!is_file($img_path)) {break;}
			}
			move_uploaded_file($file['tmp_name'], $img_path);
		} else {
			$img_url = $img_path = '';
		}
		$param = array(
			'img_url' => $img_url,
			'img_path' => $img_path,
		);
		$this->load->view('pic_upload', $param);
	}
	
	public function poi() {
		$this->parse_data('poi');
		$this->_parse_center();
		$this->collection = 'POI';
		isset($_POST['regionId']) && $_POST['regionId'] = new MongoId(strval($_POST['regionId']));
		$this->mongo_duplicate();
		header("Location: /poi/detail?_id=".$_POST['_id']);
	}
	
	public function region() {
		$this->parse_data('region');
		$this->_parse_center();
		$this->collection = 'Region';
		$this->mongo_duplicate();
		header("Location: /region/detail?_id=".$_POST['_id']);
	}
	
	public function article() {
		$this->parse_data('article');
		$this->collection = 'Article';
		$this->mongo_duplicate();
		header("Location: /article/detail?_id=".$_POST['_id']);
	}
	
	public function wikipedia() {
		$this->parse_data('article');
		$this->collection = 'Wikipedia';
		$_POST['_id_noo'] = $_POST['_id'];
		unset($_POST['_id']);
		$this->mongo_duplicate();
		header("Location: /wikipedia/detail?_id=".$_POST['_id_noo']);
	}
	
	private function parse_data($collection) {
		$config_key = $collection.'_field';
		$this->conf = $this->config->item($config_key);
		$json_field = !empty($this->conf['json']) ? array_keys($this->conf['json']) : array();
		foreach($json_field AS $field) {
			$_POST[$field] = json_decode($_POST[$field], true);
		}
	}
	
	private function _parse_center() {
		$_POST['center'] = array(floatval($_POST['lat']), floatval($_POST['lng']));
		unset($_POST['lat'], $_POST['lng']);
	}
	
	private function mongo_duplicate() {
		$func = (!empty($_POST['_id']) || !empty($_POST['_id_noo'])) ? $this->collection.'_update' : $this->collection.'_add';
		isset($_POST['wikicategory']) && $_POST['wikicategory'] = wikicategory_parse($_POST['wikicategory']);
		$res = $this->mongo_db->$func($_POST);
		!empty($_POST['_id']) && $_POST['_id'] == $res;
	}
}
