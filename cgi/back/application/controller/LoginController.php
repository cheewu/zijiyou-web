<?php
/**
 * 
 * LoginController
 * @author Coffee
 *
 */
class LoginController extends MfController 
{
  public function index() 
  {
    $this->tpl['_title'] = 'Login';
    
    $this->tpl['refer'] = rawurlencode($this->input->safeGet('refer', '/'));
    
    $this->tpl['remember_me'] = $this->input->safeGet('remember_me', '');
    
    $this->tpl['auth_fail_alert'] = $this->input->safeGet('fail', '');  
    
    $this->tpl->loadTpl('header-base')
              ->loadTpl('login/index')
              ->loadTpl('footer-base')
              ->view();
  }
  
  public function auth()
  {
    $email       = $this->input->safeGet('email', null);
    $password    = $this->input->safeGet('password', null);
    $remember_me = $this->input->safeGet('remember_me', null);
    if ($this->user->auth($email, $password)) {
      $this->user->writeCookie($email, $password, $remember_me);
      $this->user->authPass   ($email, $password);
      $this->url->redirect($this->input->safeGet('refer', '/'));
    }
    $this->input->deleteMulti(array('email', 'password'));
    $this->input->set('fail', 1);
    $this->url->redirect($this->url->makeBasic('/login', 
                         $this->input->dumpStorage()));
  }
  
  public function clear()
  {
    $this->user->deleteCookie();
    $this->url->redirect($this->input->safeGet('refer', '/'));
  }
}