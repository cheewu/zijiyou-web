<?php

class Page
{
  
  private $total = 0;
  
  private $display = 5;
  
  private $cur_page = 1;
  
  private $page_size = 20;
  
  private $callback = null;
  
  private $multi = array();
  
  public function __construct()
  {
    $this->cur_page  = Mf::$input->safeGet('pg', 1);
    $this->page_size = Mf::$config->safeGet('page.default_size', 20); 
    $this->display   = Mf::$config->safeGet('page.default_display', 5);
    $this->callback  = Mf::$config->safeGet('page.default_callback', null);
  }
  
  public function curPage($page)
  {
    $this->cur_page = $page;
    return $this;
  }
  
  public function total($total)
  {
    $this->total = $total;
    return $this;
  }
  
  public function display($display)
  {
    $this->display = $display;
    return $this;
  }
  
  public function make($callback = null)
  {
    if (!is_null($callback)) $this->callback = $callback;
    $half_dis       = intval($this->display / 2);
    $this->display  = $half_dis * 2 + 1; // 必须是奇数
    $total_page     = ceil($this->total / $this->page_size);
    if ($total_page <= 1) return '';
    do {
      $min_slot = min($this->display, $total_page); 
      $display_slot = range(1, $min_slot);
      if ($display_slot <= $total_page) break 2;
      do {
        $offset_slot = 0;
        if ($this->cur_page <= $half_dis + 1) break;
        $offset_slot = $total_page - $this->display;
        if ($this->cur_page >= $total_page - $half_dis) break;
        $offset_slot = $this->cur_page - $half_dis - 1;
      } while(0); 
      $display_slot = array_map(
                        function($v) use($offset_slot) { 
                          return $v + $offset_slot; 
                        }, 
                        $display_slot
                      );
    } while(0);
    if ($this->cur_page > 1) {
      $display_slot['prev'] = $this->cur_page - 1;
    }
    if ($this->cur_page < $total_page) {
      $display_slot['next'] = $this->cur_page + 1;
    }
    if (is_null($this->callback)) return $display_slot;
    return call_user_func_array(
             $this->callback, 
             array(
               array_intersect_key($display_slot, range(1, $min_slot)),
               $this->cur_page,
               $total_page,
             )
           );
  }
}

