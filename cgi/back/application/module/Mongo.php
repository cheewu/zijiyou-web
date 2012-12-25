<?php
/**
 * 
 * MfMongo class
 * @author Coffee
 *
 */
class MfMongo extends Mongo implements ArrayAccess 
{
  public function __construct() 
  {
    $server  = Mf::$config->get('mongo.server');
    $options = Mf::$config->get('mongo.options');
    parent::__construct($server, $options);    
  }
  
  public function __get($dbname)
  {
    return $this->selectDB($dbname);
  }
  
  /**
   * @see Mongo::selectDB()
   */
  public function selectDB($dbname)
  {
    return new MfMongoDB($this, $dbname);
  }
  
  /**
   * @see Mongo::selectCollection()
   */
  public function selectCollection($dbname, $collection = NULL)
  {
    return $this->selectDB($dbname)->selectCollection($collection);
  }
  
  /**
   * @see ArrayAccess::offsetGet()
   */
  public function offsetGet($dbname) 
  {
    if (strpos($dbname, '.') !== false) {
      list($dbname, $collection) = explode('.', $dbname, 2);
      return $this->selectCollection($dbname, $collection);
    }
    return $this->selectDB($dbname);
  }
  
  /**
   * @see ArrayAccess::offsetUnset()
   */
  public function offsetUnset($dbname) {}
  
  /**
   * @see ArrayAccess::offsetSet()
   */
  public function offsetSet($dbname, $value) {}
  
  /**
   * @see ArrayAccess::offsetExists()
   */
  public function offsetExists($dbname) {}
  
  
}

/**
 * 
 * MfMongoDB class
 * @author Coffee
 *
 */
class MfMongoDB extends MongoDB implements ArrayAccess
{
  /**
   * dbname
   * @var string
   */
  public $name;
  
  /**
   * con
   * @var Mongo
   */
  public $con;
  
  public function __construct(Mongo $con, $name)
  {
    $this->con  = $con;
    $this->name = $name;
    parent::__construct($con, $name);
  }
  
  /**
   * @see MongoDB::selectCollection()
   */
  public function selectCollection($name)
  {
    return new MfMongoCollection($this, $name);
  }
  
  /**
   * @see MongoDB::__get()
   */
  public function __get($name)
  {
    return $this->selectCollection($name);
  }
  
  /**
   * @see ArrayAccess::offsetGet()
   */
  public function offsetGet($name) 
  {
    return $this->selectCollection($name);
  }
  
  /**
   * @see ArrayAccess::offsetUnset()
   */
  public function offsetUnset($name) {}
  
  /**
   * @see ArrayAccess::offsetSet()
   */
  public function offsetSet($name, $value) {}
  
  /**
   * @see ArrayAccess::offsetExists()
   */
  public function offsetExists($name) {}
}

/**
 * 
 * MfMongoCollection class
 * @author Coffee
 *
 */
class MfMongoCollection extends MongoCollection
{
  /**
   * collection name
   * @var string
   */
  public $name;
  
  /**
   * db
   * @var MongoDB
   */
  public $db;
  
  /**
   *  @see MongoCollection::__construction()
   */
  public function __construct(MongoDB $db, $name)
  {
    $this->db   = $db;
    $this->name = $name;
    parent::__construct($db, $name);
  }
  
  /**
   * @see MongoCollection::__get()
   */
  public function __get($name)
  {
    return new self($this->db, $name);
  }
  
  /**
   * @see MongoCollection::find()
   */
  public function find($query = NULL, $fields = NULL)
  {
    if (is_null($query))  $query = array();
    if (is_null($fields)) $fields = array();
    return new MfMongoCursor($this->db->con, "{$this->db->name}.{$this->name}", 
                             $query, $fields);
  }
}

/**
 * 
 * MfMongoCursor
 * @author Coffee
 *
 */
class MfMongoCursor extends MongoCursor 
{
   public function toArray($is_nature_idx = true)
   {
     if (!$is_nature_idx) {
       return iterator_to_array($this);
     } else {
       return array_values(iterator_to_array($this));
     }
   } 
}

/**
 * 
 * MfMongoId
 * @author Coffee
 *
 */
class MfMongoId extends MongoId
{
  
}
