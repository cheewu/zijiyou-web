<?php
class Error
{
  public function __construct($errorhttpCode = null)
  {
    
  }
  
  public function view()
  {
    Mf::$tpl->loadTpl('error/500.php');
  }
  
  public function accessDeny()
  {
    Mf::$tpl->loadTpl('error/500.php');
  }
}
