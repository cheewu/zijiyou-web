<?php

class ListController extends MfController
{
  
  public function _preAction()
  {
    $this->page_num_  = $this->input->safeGet('pg', 1); 
    $this->page_num_  = abs(intval($this->page_num_));
    $this->page_size_ = $this->config->safeGet('page.defalut_size', 20);
    $this->rid_       = $this->input->safeGet('rid', '');
    $this->sort_      = $this->input->safeGet('sort', '');
    $this->q_         = $this->input->safeGet('q', '');
    $this->ct_        = $this->input->safeGet('ct', '');
    $this->query_     = array();
    if (strlen($this->sort_) > 0) {
      $this->sort_ = array(substr($this->sort_, 1) => 
                          substr($this->sort_, 0, 1) == '+' ? 1 : -1);
    }
    if (!empty($this->rid_)) {
      $this->region_ = $this->mongo['tripfm.Region']
                            ->findOne(array('_id' => new MongoId($this->rid_))); 
    }
  }
  
  public function region()
  {
    $this->tpl['col'] = 'Region'; 
    
    $this->accessControl(__FUNCTION__, true);
    
    if ($this->input->safeGet('ipt', false)) {
      $this->query_['is_important'] = true;
    }
    
    $this->parseCategory();
    
    $iterator = $this->mongo['tripfm.Region']
                     ->find($this->query_)->sort($this->sort_)
                     ->skip(($this->page_num_ - 1) * $this->page_size_)
                     ->limit($this->page_size_);
    $this->view($iterator, __FUNCTION__);
  }
  
  public function subway()
  {
    $this->tpl['col'] = 'Subway';
    
    $iterator = $this->accessControl(__FUNCTION__);
    
    $this->view($iterator, __FUNCTION__);
  }
  
  public function poi()
  {
    $this->tpl['col'] = 'POI';
    
    $iterator = $this->accessControl(__FUNCTION__);
    
    $this->view($iterator, __FUNCTION__);
  }
  
  private function parseQ($is_region = false) 
  {
    if (empty($this->q_)) return;
    $qf = $this->input->safeGet('qf', 'name');
    do {
      if ($qf == 'name') break;
      if ($is_region)    break;
      $region = $this->mongo['tripfm.Region']
                     ->findOne(array('name' => $this->q_));
      if (empty($region)) {
        $this->query_['regionId'] = 'not found';
        return;
      }
      $this->rid_ = strval($region['_id']);
      $this->query_['regionId'] = $region['_id'];
      return;
    } while(0);
    $this->query_[$qf] = $this->q_;
  }
  
  private function parseCategory()
  {
    if (!empty($this->ct_)) {
      $this->query_['category'] = $this->ct_;
    }
  }
  
  private function parseTitle()
  {
    $this->tpl['TITLE'] = $this->tpl['col'] . ' List';
    
    if (!empty($this->q_)) {
      $this->tpl['TITLE'] = $this->q_ . ' List';
    }
    
    if (!empty($this->region_)) {
      $this->tpl['TITLE'] = $this->region_['name'] . ' List';
    }
  }
  
  private function accessControl($set, $is_region = false)
  {
    $id_field = $is_region ? '_id' : 'regionId';
    
    if (!$this->global['user.is_sudoer'] && !empty($this->rid_)) {
      if (!$this->user->authAccess($this->rid_)) {
        MfUrl::redirect($this->url->makeOrigin(array(), MfUrl::Q_NEW));
      }
    }
    
    if (empty($this->rid_)) {
      if (!$this->global['user.is_sudoer']) {
        $this->query_[$id_field] = array('$in' => array_map(function($v) {
          return new MongoId($v);
        }, $this->global['user.allow']));
      }
    } else {
      $this->query_[$id_field] = new MongoId($this->rid_);
    }
    
    if (empty($this->sort_)) {
      $this->sort_ = $this->config->safeGet("$set.default_sort", array('name' => -1));
    } 
    
    $this->parseQ($is_region);
    $this->parseCategory();
    
    $iterator  = $this->mongo[sprintf('tripfm.%s', $this->tpl['col'])]
                      ->find($this->query_)->sort($this->sort_)
                      ->skip(($this->page_num_ - 1) * $this->page_size_)
                      ->limit($this->page_size_);
    
    return $iterator;
  }
  
  private function view(MfMongoCursor $iterator, $colName)
  {
    $multi = new Page();
    $this->parseTitle();
    $this->tpl['q']     = $this->q_;
    $this->tpl['rid']   = $this->rid_;
    $this->tpl['sort']  = array('field' => key($this->sort_), 'order' => current($this->sort_));
    $this->tpl['multi'] = $multi->total($iterator->count())
                                ->curPage($this->page_num_)->make();
    $this->tpl['list'] = $iterator->toArray();
    $this->tpl['fields'] = $this->config->get("$colName.list_fields");
    $this->tpl['sort_fields'] = $this->config->safeGet("$colName.sort_fields", array());
    $this->tpl->loadTpl('header')->loadTpl("list/$colName")->loadTpl('footer')
              ->view();
  }
}