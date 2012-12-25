<?php
class AjaxController extends MfController
{
  
  private $response_ = array(
    'code'     => 100,
    'errorMsg' => '',
  );
  
  private $data_ = array();
  
  public function _preAction()
  {
    $this->data_ = $this->input->safeGet('data', array());
  }
  
  public function submit($col)
  {
    $col = strtolower($col);
    $this->$col();
  }
  
  private function subway() 
  {
    $response = array(
      'code'     => 100,
      'errorMsg' => '',
    ); 
    $data = $this->input->get('data');
    
    // get origin subway
    $subway = $this->mongo['tripfm.Subway']
                   ->findOne(array('_id' => new MongoId($data['subwayId'])));
    $subline_arr_origin = $subway['subline'];
    
    // handle new subline and poi subway
    $subline_arr_new = array(); $poi_subway_map = array();
    foreach ($data['subline'] as $sublineId => $subline) {
      if (substr($sublineId, 0, 4) == 'new-') {
        $sublineId = g_mongo_inc('Subway', 'sublineId');
      } else {
        $sublineId = intval($sublineId);
      }
      $subline_new = array(
        'id'   => $sublineId,
        'name' => $subline['name'],
        'list' => array(),
      );
      if (empty($subline['list'])) $subline['list'] = array();
      foreach ($subline['list'] as $station) {
        $subline_new['list'][] = array(
          'order'  => intval($station['order']),
          'poiId'  => new MongoId($station['poiId']),
          'direct' => intval($station['direct'])
        );
        $poi_subway_map[$station['poiId']][] = $sublineId;
      }
      $subline_arr_new[] = $subline_new;
      unset($subline_new);
    }
    
    
    // find station remove from subline
    foreach ($subline_arr_origin as $subline_origin) {
      foreach ($subline_origin['list'] as $station_origin) {
        $is_remove = true;
        // 把每一个老的子站点 去新的子站点中挨个查询, 如果没有, 则认为其被删除
        foreach ($subline_arr_new as $subline_idx => $subline_new) {
          foreach ($subline_new['list'] as $station_idx => $station_new) {
            if ($subline_origin['id'] != $subline_new['id']) continue;
            if ($station_origin['poiId'] != $station_new['poiId']) continue;
            $is_remove = false;
            // 判断分钟字段是否存在, 如果存在, 补上
            if (!isset($station_origin['minute'])) continue;
            $subline_arr_new[$subline_idx]['list'][$station_idx]['minute'] = 
              $station_origin['minute'];
          }
        } 
        // end new foreach
        if ($is_remove == false) continue;
        $remove_data = array(
          'order'     => $station_origin['order'],
          'lineId'    => $subway['lineId'],
          'sublineId' => $subline_origin['id'],
          'poiId'     => $station_origin['poiId']
        );
        // 有可能存在同一个站在一条线中出现两次的情况, 比如环线
        $has_count = 0;
        foreach ($subline_origin['list'] as $station_double_check) {
          if ($station_origin['poiId'] != $station_double_check['poiId']) continue;
          $has_count ++;
        }
        // 由于poi中subway的subline字段不允许重复
        // 当原线路中的重复站点被去除时, 不需要把poi中的子线id拉出
        if ($has_count == 1) { 
          $resp = $this->_removeStationFromSubline($remove_data);
          if ($resp['code'] != 100) $response = $resp;
        }
      }
    }
    
    
    // get original stations
    $station_arr_origin = $this->mongo['tripfm.POI']
                               ->find(array('regionId'      => $subway['regionId'],
                                            'category'      => 'subway',
                                            'subway.lineId' => $subway['lineId']),
                                      array('name'))
                               ->toArray();
    
                                 
    // find station remove or update from line
    foreach ($station_arr_origin as $station_origin) {
      $poiId = strval($station_origin['_id']);
      if (!isset($data['station'][$poiId])) {
        $remove_data = array(
          'lineId' => $subway['lineId'],
          'poiId'  => $poiId
        );
        $resp = $this->_removeStationFromLine($remove_data);
        if ($resp['code'] != 100) $response = $resp;
      } elseif ($data['station'][$poiId] != $station_origin['name']) {
        $update_data = array(
          'poiId' => $poiId,
          'name'  => $data['station'][$poiId]
        );
        $resp = $this->_updateStationName($update_data);
        if ($resp['code'] != 100) $response = $resp;
      }
    }
    
    // update subway
    $update = $data['fields'];
    $update['subline'] = $subline_arr_new;
    
    
    $res = $this->mongo['tripfm.Subway']
                ->update(array('_id'  => $subway['_id']),
                         array('$set' => $update), 
                         array('safe' => true));
    if ($res['ok'] != 1) { 
      $response['code'] = 200;
      $response['errorMsg'] = $res['err'];
    }
    
    // update poi
    foreach ($poi_subway_map as $poiId => $list) {
      // 处理新的地铁线关系
      $subline = array(); $uniq = array();
      foreach ($list as $sublineId) { 
        if(isset($uniq[$sublineId])) continue;
        $subline[] = array('id' => $sublineId);
        $uniq[$sublineId] = true;
      }
      
      $res = $this->mongo['tripfm.POI']
                  ->update(array('_id' => new MongoId($poiId),
                                 'subway.lineId' => intval($data['lineId'])),
                           array('$set' => array(
                             'subway.$.subline' => $subline
                           )),
                           array('safe' => true));
      
      if ($res['ok'] != 1) { 
        $response['code'] = 200;
        $response['errorMsg'] = $res['err'];
      }
    }
    die(json_encode($response));
  }
  
  public function mongo($method)
  {
    $method = strtolower($method);
    $this->$method();
  }
  
  private function removeStationFromLine()
  {
    $data = $this->input->get('data');
    $response = $this->_removeStationFromLine($data);
    die(json_encode($response));
    
  }
  
  private function _removeStationFromLine($data)
  {
    $response = array(
      'code'     => 100,
      'errorMsg' => '',
    );
    
    // to int
    $data['lineId'] = intval($data['lineId']);
    
    if (!($data['poiId'] instanceof MongoId)) {
      $data['poiId'] = new MongoId($data['poiId']);
    }
    
    $subway = $this->mongo['tripfm.Subway']->findOne(array('lineId' => $data['lineId']));
    foreach ($subway['subline'] as $subline) {
      $old_list = $new_list = array();
      foreach ($subline['list'] as $station) {
        $old_list[intval($station['order'])] = $station;
      }
      ksort($old_list); $is_delete = 0;
      foreach ($old_list as $station) {
        if ($station['poiId'] == $data['poiId']) {
          $is_delete ++; continue;
        }
        $station['order'] -= $is_delete;
        $new_list[] = $station;
      }
      if ($is_delete == 0) continue;
      $res = $this->mongo['tripfm.Subway']
                  ->update(array('lineId' => $data['lineId'],
                                 'subline.id' => $subline['id'],
                           array('$set' => array('subline.$.list' => $new_list))),
                           array('safe' => true));
      if ($res['ok'] != 1) { 
        $response['code'] = 200;
        $response['errorMsg'] = $res['err'];
      }
    }
    $res = $this->mongo['tripfm.POI']
                ->update(array('_id'           => $data['poiId'],
                               'subway.lineId' => $data['lineId']),
                         array('$unset' => array('subway.$' => true)),
                         array('safe' => true));
    if ($res['ok'] != 1) { 
      $response['code'] = 200;
      $response['errorMsg'] = $res['err'];
    }
    $res = $this->mongo['tripfm.POI']
                ->update(array('_id' => new MongoId($data['poiId'])),
                         array('$pull' => array('subway' => null)),
                         array('safe' => true));
    if ($res['ok'] != 1) { 
      $response['code'] = 200;
      $response['errorMsg'] = $res['err'];
    }
    
    return $response;
  }
  
  private function removeStationFromSubline()
  {
    $data = $this->input->get('data');
    $response = $this->_removeStationFromSubline($data);
    die(json_encode($response));
  }
  
  private function _removeStationFromSubline($data)
  {
    $response = array(
      'code'     => 100,
      'errorMsg' => '',
    );
    
    // to int
    $data['order']     = intval($data['order']);
    $data['lineId']    = intval($data['lineId']);
    $data['sublineId'] = intval($data['sublineId']);
    if (!($data['poiId'] instanceof MongoId)) {
      $data['poiId'] = new MongoId($data['poiId']);
    }
    
    $subway = $this->mongo['tripfm.Subway']->findOne(array('lineId' => $data['lineId']));
    foreach ($subway['subline'] as $subline) {
      if ($subline['id'] != $data['sublineId']) continue;
      $old_list = $new_list = array();
      foreach ($subline['list'] as $station) {
        $old_list[intval($station['order'])] = $station;
      }
      ksort($old_list); $is_delete = 0;
      foreach ($old_list as $station) {
        if ($station['poiId'] == $data['poiId'] &&
            $station['order'] == $data['order']) {
          $is_delete ++; continue;
        }
        $station['order'] -= $is_delete;
        $new_list[] = $station;
      }
    }
    
    if ($is_delete == 0) return $response;
    $res = $this->mongo['tripfm.Subway']
                ->update(array('lineId'     => $data['lineId'],
                               'subline.id' => $data['sublineId']),
                         array('$set' => array('subline.$.list' => $new_list,
                                               'stationCount' => count($new_list))),
                         array('safe' => true));
                         
    if ($res['ok'] != 1) { 
      $response['code'] = 200;
      $response['errorMsg'] = $res['err'];
    }
    $res = $this->mongo['tripfm.POI']
                ->update(array('_id'           => $data['poiId'],
                               'subway.lineId' => $data['lineId']),
                         array('$pull' => array('subway.$.subline' => 
                                                array('id' => $data['sublineId']))),
                         array('safe' => true));
    if ($res['ok'] != 1) { 
      $response['code'] = 200;
      $response['errorMsg'] = $res['err'];
    }
    return $response;
  }
  
  
  private function updateStationName()
  {
    $data = $this->input->get('data');
    $response = $this->_updateStationName($data);
    die(json_encode($response));
  }
  
  private function _updateStationName($data)
  {
    $response = array(
      'code'     => 100,
      'errorMsg' => '',
    );
    $res = $this->mongo['tripfm.POI']
                ->update(array('_id' => new MongoId($data['poiId'])),
                         array('$set' => array('name' => trim($data['name']))),
                         array('safe' => true));
    if ($res['ok'] != 1) { 
      $response['code'] = 200;
      $response['errorMsg'] = $res['err'];
    }
    return $response;
  }
  
  private function addSubwayStation()
  {
    $response = array(
      'code'     => 100,
      'errorMsg' => '',
    );
    
    // get data
    $data = $this->input->get('data');
    $data['lineId'] = intval($data['lineId']);
    
    // check exists
    $query = array(
      'regionId' => new MongoId($data['regionId']),
      'category' => 'subway',
      'name'     => trim($data['name']),
    );
    
    // find if exists
    $poi = $this->mongo['tripfm.POI']->findOne($query, array('subway'));
    
    $poi_subway_new = array(
      'lineId' => $data['lineId'],
      'subline' => array()
    );
    
    do {
      // 不存在
      if (empty($poi)) {
        $query['subway'] = array($poi_subway_new);
        $res = $this->mongo['tripfm.POI']->insert($query, array('safe' => true));
        if ($res['ok'] != '1') {
          $response['code'] = 200;
          $response['errorMsg'] = $res['errmsg'];
          break; // 错误
        }
        $response['pid'] = strval($query['_id']);
        break; // 新插入成功
      }
      
      $response['pid'] = strval($poi['_id']);
      
      // poi存在 poi.subway不存在
      if (empty($poi['subway'])) {
        $response['pid'] = strval($poi['_id']);
        $res = $this->mongo['tripfm.POI']
                    ->update(array('_id' => $poi['_id']), 
                             array('$set' => array(
                               'subway' => array($poi_subway_new)
                             )),
                             array('safe' => true));
        if ($res['ok'] != '1') {
          $response['code'] = 200;
          $response['errorMsg'] = $res['errmsg'];
        }
        break;
      }
      
      // poi存在 poi.subway字段也存在的情况 
      // 判断lineId是否存在
      $is_lineid_exists = false;
      $poi_subway = $poi['subway'];
      foreach ($poi['subway'] as $subway) {
        if ($subway['lineId'] != $data['lineId']) continue;
        $is_lineid_exists = true;
      }
      if ($is_lineid_exists == true) {
        // lineId存在 break
        $response['code'] = 200;
        $response['errorMsg'] = '该站点已经存在, 请勿再次添加';
      } 
      
      // lineId不存在, push
      $res = $this->mongo['tripfm.POI']
                    ->update(array('_id' => $poi['_id']), 
                             array('$push' => array(
                               'subway' => $poi_subway_new
                             )),
                             array('safe' => true));
      if ($res['ok'] != '1') {
        $response['code'] = 200;
        $response['errorMsg'] = $res['errmsg'];
      }
    } while(0); 
    
    die(json_encode($response));
  }
  
  public function search($col) 
  {
    if ($col != 'region') {
      $this->response_ = array(
        'code'     => 200,
        'errorMsg' => 'invalid method'
      );
      $this->response(); 
    }
    $region = $this->mongo['tripfm.Region']
                   ->findOne(array('name' => $this->data_['name']));
    if (empty($region)) {
      $this->response_ = array(
        'code'     => 200,
        'errorMsg' => "{$this->data_['name']} not exists"
      );
      $this->response();
    }
    
    $this->response_['rid'] = $region['_id']->{'$id'};
    $this->response(); 
  }
  
  public function near($col)
  {
    if ($col != 'poi') {
      $this->response_ = array(
        'code'     => 200,
        'errorMsg' => 'invalid method'
      );
      $this->response();
    }
    $center = array(floatval($this->data_['lat']), 
                    floatval($this->data_['lng']));
    $command = array(
      'geoNear'     => 'POI',
      'near'        => $center, 
      'includeLocs' => true, 
      'maxDistance' => g_dis2radian(3),
      'num'         => 50,
    );       
    $res = $this->mongo['tripfm']->command($command);
    if ($res['ok'] != 1) {
      $this->response_ = array(
        'code'     => 200,
        'errorMsg' => "not found"
      );
    } else {
      $this->response_['data'] = array();
      foreach ($res['results'] as $value) {
        if ($value['obj']['name'] == $this->data_['name']) continue;
        if (count($this->response_['data']) >= 10) break;
        $dis = g_radian2dis($value['dis']);
        $this->response_['data'][] = 
          sprintf('<div class="well well-small">
                    <span class="label label-info">%sm</span>
                    <span class="label">%s</span>
                    <span class="label label-warning">%s</span>
                    <button class="close delete-nearbypoi">&times;</button>
                    <input name="nearByPOI[]" type="hidden" value="%s"/>
                  </div>', 
                  $dis, 
                  $value['obj']['name'], 
                  $value['obj']['category'], 
                  "{$value['obj']['_id']->{'$id'}}|$dis");
      }
    }
    $this->response();
  }
  
  private function response()
  {
    die(json_encode($this->response_));
  }
}