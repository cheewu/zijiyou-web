<?php
class Ajax extends CI_Controller {
	
	function __construct() {
		parent::__construct();
	}
	
	function pic_upload() {
		echo json_encode($_FILES);
	}
	
	function delete($collection, $id) {
	    $func = $collection.'_update';
	    if($this->mongo_db->$func(array('is_del' => true), array('_id' => $id))) {
	        exit('1');
	    } else {
	        exit('0');
	    }
	}
	
	function regionSearch($name) {
	  $name = rawurldecode($name);
	  header("Content-type: text/html; charset=utf-8");
	  $res = $this->mongo_db->Region_fetchall(array(
	  								'name' => array('$regex' => "^$name"),
	  								'$or' => array(
                                        array('is_del' => array('$exists' => false)), 
                                        array('is_del' => false)
                                     )), null, array("name" => 1));
	  $html = $id = "";
	  foreach ($res['item'] AS $index => $region) {
	    if(!$index) {
	        $id = $region['_id'];
	    	$select = 'selected="selected"';
	    } else {
	        $select = "";
	    }
	    $html .= <<<HTML
	    <option value="{$region['_id']}" $select>{$region['name']}</option>
HTML;
	  }
	  echo json_encode(array("id" => $id, "html" => $html));
	}
}