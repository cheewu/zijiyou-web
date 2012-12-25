<?php
/**
 * 
 * 由于静态成员使用前必须声明
 * 所以再添加自定义模块前需要在这个类中进行声明
 * 
 * 初始化赋值的内容为该成员所采用的对象
 * @example 
 *        1, public static $mongo;
 *           Mf::$mongo = new Mongo();
 *        2, public static $mongo = 'MfMongo';
 *           Mf::$mongo = new MfMongo();
 * 
 * !!!注意!!!
 *   请勿修改此类名!!!
 *   请勿在此类中定义任何方法!!!
 *
 */
class MfModule // 请勿修改名称
{
  /**
   * module of user
   * @package User
   */
  public static $user;
  
  /**
   * module of mongo
   * @package Mongo
   */
  public static $mongo = 'MfMongo';
  
  /**
   * module of error
   * @package Error
   */
  public static $error;
}
