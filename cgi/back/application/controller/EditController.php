<?php


class EditController extends MfController
{
  public function _preAction() 
  {
    $this->tpl->loadLib('jquery.form');
  }
  
  public function subway()
  {
    $this->tpl['col'] = 'Subway';
    $id = $this->input->get('id');
    $subway = $this->mongo['tripfm.Subway']->findOne(array('_id' => new MongoId($id)));
    
    $this->tpl['TITLE'] = $subway['name'];
    
    $region = $this->mongo['tripfm.Region']->findOne(array('_id' => $subway['regionId']));
    
    if (!$this->user->authAccess($region['_id'])) {
      return $this->error->accessDeny();
    }
    $this->tpl['stations'] = $this->mongo['tripfm.POI']
                                  ->find(array('regionId'      => $region['_id'],
                                               'category'      => 'subway',
                                               'subway.lineId' => $subway['lineId']),
                                         array('name'))
                                  ->sort(array('name' => 1))
                                  ->toArray();
    $this->tpl['detail'] = $subway;
    $this->tpl['region'] = $region;
    $this->tpl['fields'] = $this->config->get('subway.edit_fields');
    $this->tpl->loadScript('drag.js')
              ->loadTpl('header')
              ->loadTpl('edit/subway')
              ->loadTpl('footer')
              ->view();
  }
  
  public function region()
  {
    $id = $this->input->get('id');
    $region = $this->mongo['tripfm.Region']->findOne(array('_id' => new MongoId($id)));
    $this->tpl['TITLE'] = $region['name'];
    
    $this->tpl['detail'] = $region;
    $this->tpl['fields'] = $this->config->get('region.edit_fields');
    $this->tpl->loadScript('http://ditu.google.cn/maps/api/js?sensor=false')
              ->loadScript('map.js')
              ->loadTpl('header')
              ->loadTpl('edit/region')
              ->loadTpl('footer')
              ->view();
  }
  
  public function poi()
  {
    $id = $this->input->get('id');
    $poi = $this->mongo['tripfm.POI']->findOne(array('_id' => new MongoId($id)));
    $this->tpl['TITLE'] = $poi['name'];
    
    $this->tpl['detail'] = $poi;
    $this->tpl['fields'] = $this->config->get('poi.edit_fields');
    $this->tpl->loadScript('http://ditu.google.cn/maps/api/js?sensor=false')
              ->loadScript('map.js')
              ->loadTpl('header')
              ->loadTpl('edit/poi')
              ->loadTpl('footer')
              ->view();
  }
  
  public function update($col)
  {
    $response = array(
      'code'     => 100,
      'errorMsg' => '' 
    );
    $data = $this->input->dumpStorage();
    $id = new MongoId($data['_id']);
    unset($data['_id']);
    if (isset($data['center'])) { 
      $data['center'] = array_map('floatval', $data['center']);
    }
    switch (strtolower($col)) {
      case 'region':
        $res = $this->mongo['tripfm.Region']
                    ->update(array('_id'  => $id),
                             array('$set' => $data),
                             array('safe' => true));
      break;
      case 'poi':
        if (!empty($data['nearByPOI'])) {
          $data['nearByPOI'] = array_map(function($v) {
            list($id, $dis) = explode("|", $v);
            return array(
              'pid' => new MongoId($id),
              'dis' => $dis,
            );
          }, $data['nearByPOI']);
        }
        $data['regionId'] = new MongoId($data['regionId']);
        $res = $this->mongo['tripfm.POI']
                    ->update(array('_id'  => $id),
                             array('$set' => $data),
                             array('safe' => true));
      default: break;
    }
    if ($res['ok'] != 1) { 
      $response['code'] = 200;
      $response['errorMsg'] = $res['err'];
    }
    die(json_encode($response));
  }
}