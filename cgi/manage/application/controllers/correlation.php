<?php
class Correlation extends CI_Controller {
  
    function index() {
        $this->title = "correlation manage";
        $pg = $this->input->get('pg');
        $q = $this->input->get('q');
        ($pg <= 0 || empty($pg)) && $pg = 1;
        $this->ps = 30;
        $res = $this->mongo_db->con->page->
          command(array("distinct" => "correlation", "key" => "correlation"));
        $fields = array_keys(call_user_func_array("array_merge_recursive", $res['values']));
        sort($fields);
        array_unshift($fields, "name");
        $query = array(
            '$or' => array(
                array('is_del' => array('$exists' => false)), 
                array('is_del' => false)
             ),
        );
        if($q) { $query['name'] = array('$regex' => $q);}
        $iterator = $this->mongo_db->con->page->correlation->find($query)->sort(array('_id' => -1));
        $this->count = $iterator->count();
        $iterator =  $iterator->skip(($pg - 1) * $this->ps)->limit($this->ps);
        $res = array_values(iterator_to_array($iterator));
        foreach ($res AS $index => $item) {
            foreach ($item['correlation'] AS $field => $data) {
                $res[$index][$field] = implode(", ", array_keys($data));
            }
            unset($res[$index]['correlation']);
        }
        $param = array(
			'lists'  => $res,
			'fields' => $fields,
		);
		$this->load->view('correlation_list', $param);
        /*
        pr(array_keys($res), 0);
        $res = $this->mongo_db->con->page->correlation->update(
            array('_id' => new MongoId("4ffe3e83e4b05380f7e36341")),
            array(
            	'$push' => array("correlation.test" => array("b" => 2))
            )
        );
        pr($res);
        */
    }
    
    function item($correlation_id) {
        $correlation = $this->mongo_db->con->page->correlation->
          findOne(array('_id' => new MongoId($correlation_id)));
        ksort($correlation['correlation']);
        $param = array(
			'correlation'        => $correlation,
		);
		$this->title = $correlation['name'];
        $this->load->view('correlation', $param);
    }
    
    function post() {
        $id = $_POST['_id'];
        unset($_POST['_id']);
        $correlation = array();
        foreach ($_POST AS $field => $category) {
            foreach ($category AS $index => $data) {
                list($name, $score) = explode(":", $data);
                $correlation[$field][$name] = $score;
            }
        }
        $this->mongo_db->con->page->correlation->update(
            array('_id' => new MongoId($id)),
            array('$set' => array('correlation' => $correlation))
        );
        header("Location: /correlation/item/$id");
    }
}