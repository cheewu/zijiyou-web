<?php
class Subway extends CI_Controller {
  
  public $fields = array(
    'region', 'system',      'name', 'color', 'lineid', 'numberOfStation',
    'length', 'stationList', 'wiki',
  );
  
  public $station_fields = array(
    'wikititle', 'name', 'stationOrder', 'stationMinute', 'transferLine',
    'poiId',
  );
  
  public $ps = 30;
  
  function index() {
    $this->title = "Subway manage";
    $pg = $this->input->get('pg');
    ($pg <= 0 || empty($pg)) && $pg = 1;
    $this->q = $this->input->get('q');
    // query
    $query = array();
    if ($this->q) $query['region'] = array('$regex' => "$this->q");
    $iterator = $this->mongo_db->con->tripfm->Subway->find($query)->sort(array('region' => -1));
    $this->count = $iterator->count();
    $iterator =  $iterator->skip(($pg - 1) * $this->ps)->limit($this->ps);
    $res = array_values(iterator_to_array($iterator));
    $param = array(
      'lists'  => $res,
      'fields' => $this->fields,
    );
    $this->load->view('subway_list', $param);
  }
  
  function add_line() {
    $region = $this->input->get('region');
    $insert = array_combine($this->fields, array_fill(0, count($this->fields), ''));
    $insert['region'] = $region;
    $res = $this->mongo_db->con->tripfm->Subway->insert($insert);
    $id = strval($insert['_id']);
    header("Location: /subway/item/$id");
  }
  
  function item($subway_id) {
    $tripfm = $this->mongo_db->con->tripfm;
    $subway = $tripfm->Subway->
      findOne(array('_id' => new MongoId($subway_id)));
    foreach ($subway['stationList'] AS $k => $value) {
      $poi = $tripfm->POI->findOne(array('_id' => $value['poiId']));
      $subway['stationList'][$k]['wikititle'] = $poi['wikititle'];
      $subway['stationList'][$k]['name']      = $poi['name'];
    }
    $param = array(
      'subway' => $subway
    );  
    $this->title = $subway['name'];
    $this->load->view('subway', $param);
  }
  
  function post() {
    $tripfm = $this->mongo_db->con->tripfm;
    $id = $_POST['_id']; unset($_POST['_id']);
    $stationList = $_POST['stationList'];
    $_POST['stationList'] = array();
    foreach($stationList AS $k => $v) {
      $_POST['stationList'][$k] = array(
        'poiId'         => new MongoId($v['poiId']),
        'stationOrder'  => intval($v['stationOrder']),
        'stationMinute' => intval($v['stationMinute']),
        'transferLine'  => json_decode($v['transferLine'], true),
      );
      $poiId = $v['poiId'];
      $poi = $tripfm->POI->findOne(array('_id' => new MongoId($poiId)));
      $line = $poi['line'];
      $line[$_POST['lineid']] = array(
        'name'  => $_POST['name'],
        'order' => $v['stationOrder'],
      );
      $poi_data = array(
        'wikititle' => fan2jian($v['wikititle']), 
//        'area'      => $poi['area'],
//        'regionId'  => $poi['regionId'],
        'name'      => fan2jian($v['name']),
        'line'      => $line,
      );
      if (empty($poi['center'])) {
        $poi_data['center'] = get_wiki_center($poi_data['wikititle']);
      }
      $tripfm->POI->update(array('_id' => new MongoId($poiId)), 
                           array('$set' => $poi_data));
    }
    $_POST['numberOfStation'] = count($_POST['stationList']);
    $res = $tripfm->Subway->update(
      array('_id' => new MongoId($id)),
      array('$set' => $_POST),
      array('safe' => true)
    );
    header("Location: /subway/item/$id");
  }
  
  function delete($id) {
    if ($this->mongo_db->con->tripfm->Subway->remove(
      array('_id' => new MongoId($id)),
      true)
    ) die("1");
    die("fail");
  }
  
  function delete_station($poiId, $lineid) {
    $res = array(
             'response_code' => '200',
             'response_msg'  => '',
             'response_data' => '',
    );
    $lineid = intval($lineid);
    $tripfm = $this->mongo_db->con->tripfm;
    $poi = $tripfm->POI->findOne(array('_id' => new MongoId($poiId)));
    unset($poi['line'][$lineid]);
    if (!$tripfm->POI->update(
      array('_id' => $poi['_id']), 
      array('$set' => array('line' => $poi['line'])))
    ) { $res['response_msg'] = 'remove error'; goto response; };
    $res['response_code'] = 100;
  response:
    die(json_encode($res));
  }
  
  function add_station() {
    $res = array(
             'response_code' => '200',
             'response_msg'  => '',
             'poiId'         => '',
             'line'          => '',
    );
    foreach (array('wikititle', 'name') AS $v) {
      $_GET[$v] = trim($_GET[$v]);
      if (!empty($_GET[$v])) continue;
      $res['response_msg'] = "$v is missing";
      goto response;
    }
    $param = array(
      'regionId' => new MongoId(trim($_GET['regionId'])),
      'area' => fan2jian($_GET['region']),
      'name' => fan2jian($_GET['name']),
      'wikititle' => $_GET['wikititle'],
      'category'  => 'subway',
    );
    $tripfm = $this->mongo_db->con->tripfm;
    $poi = $tripfm->POI->findOne($param);
    if (empty($poi)) {
      $param['center'] = get_wiki_center($param['wikititle']);
      if (!$this->mongo_db->con->tripfm->POI->insert($param)) {
        $res['response_msg'] = "insert db error"; goto response;
      }
    } else {
      $param['_id'] = $poi['_id'];
    }
    $res['poiId'] = strval($param['_id']);
    $res['line']  = empty($poi) ? "" : implode(",", array_keys($poi['line']));
    $res['response_code'] = 100;
  response:
    die(json_encode($res));
  }
}