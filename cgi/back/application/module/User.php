<?php

class User 
{
  /**
   * user password map
   * @var array
   */
  private $user = array();
  
  /**
   * allow regionId map
   * @var array
   */
  private $allow = array();
  
  /**
   * sudo list
   * @var array
   */
  private $sudoer = array();
  
  /**
   * cookie config
   * @var array
   */
  private $config = array();
  
  /**
   * function __construct
   */
  public function __construct() 
  {
    $this->user   = Mf::$config->get('auth.user');
    $this->allow  = Mf::$config->get('auth.allow');
    $this->sudoer = Mf::$config->get('auth.sudoer');
    $this->config = Mf::$config->getSection('cookie')
                               ->getMulti(array('name', 'expire', 'domain'));
  }
  
  /**
   * auth
   * @param string $email
   * @param string $password
   * @param bool   $is_encrypt
   * @return bool
   */
  public function auth($email, $password, $is_encrypt = false)
  {
    if (is_null($email) || is_null($password)) return false;
    if (!isset($this->user[$email])) return false;
    return ($password == ($is_encrypt ? $this->encrypt($email, $this->user[$email]) : 
                                        $this->user[$email]));
  }
  
  /**
   * auth cookie
   * @return bool
   */
  public function authCookie()
  {
    if (!isset($_COOKIE[Mf::$config->get('cookie.name')])) return false;
    $cookie = $_COOKIE[Mf::$config->get('cookie.name')];
    list($email, $encrypt_password) = $this->decodeCooide($cookie);
    if ($this->auth($email, $encrypt_password, true) == true) {
      $this->authPass($email); return true;
    }
    $this->deleteCookie(); return false; // 验证失败, 删除cookie
  }
  
  /**
   * auth pass write user info to Mf::$global
   * @param string $email
   */
  public function authPass($email)
  {
    Mf::$global['user'] = array(
      'email'     => $email,
      'allow'     => isset($this->allow[$email]) ? $this->allow[$email] : array(),
      'is_sudoer' => in_array($email, $this->sudoer),
    );
  }
  
  /**
   * auth access to regionid
   * @param mix $regionId
   */
  public function authAccess($regionId)
  {
    if (!Mf::$global->exists('user')) return $this->gotoLogin();
    if (Mf::$global['user.is_sudoer']) return true;
    return in_array(strval($regionId), Mf::$global['user.allow']);
  }
  
  /**
   * goto Login Page
   */
  public function gotoLogin()
  {
    Mf::$url->redirect('/login/?refer=' . rawurlencode(Mf::$url->makeOrigin()));
    exit;
  }
  
  /**
   * write auth cookie
   * @param string $email
   * @param string $password
   * @param bool   $is_remember
   * @return bool
   */
  public function writeCookie($email, $password, $is_remember)
  {
    return setcookie($this->config['name'], 
                     $this->encodeCookie($email, $password), 
                     !empty($is_remember) ? time() + $this->config['expire'] : 0,
                     '/', $this->config['domain']);
  }
  
  /**
   * delete cookie
   * @return bool
   */
  public function deleteCookie()
  {
    return setcookie($this->config['name'], NULL, strtotime('2000-01-01'),
                     '/', $this->config['domain']);
  }
  
  /**
   * encrypt cookie
   * @param string $email
   * @param string $password
   * @return base64string string
   */
  private function encodeCookie($email, $password)
  {
    return base64_encode($email . $this->encrypt($email, $password));
  }
  
  /**
   * decrypt cookie
   * @param base64string $cookie
   * @return decode string
   */
  private function decodeCooide($cookie)
  {
    $decode = base64_decode($cookie);
    return array(substr($decode, 0, -32), substr($decode, -32));
    
  }
  
  /**
   * password encrypt 
   * protect user password
   * @param string $email
   * @param string $password
   * @return encrypt password
   */
  private function encrypt($email, $password)
  {
    return md5(crc32($email) . $password . crc32($password));
  }
  
}


