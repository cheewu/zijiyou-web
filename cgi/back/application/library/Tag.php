<?php

class Tag 
{
  private static $no_content_tags = array(
    'area', 'base', 'br', 'col', 'frame', 'hr', 
    'img', 'input', 'link', 'meta', 'param',
  );
  
  public $tag_name;
  
  private $attributes;
  
  private $contents;
  
  private $child;
  
  private $father;
  
  public function __construct($tag_name, Tag $father = null)
  {
    $this->init();
    $this->tag_name = $tag_name;
    $this->father   = $father;
  }
  
  public function init()
  {
    $this->attributes = array();
    $this->contents   = '';
    $this->child      = null;
    $this->father     = null;
  }
  
  public function attr($k, $v)
  {
    $this->attributes[$k] = $v;
    return $this;
  }
  
  public function attrMulti(array $attributes)
  {
    $this->attributes = array_merge($this->attributes, $attributes);
    return $this;
  }
  
  public function contents($contents)
  {
    $this->contents = strval($contents);
    return $this;
  }
  
  public function child($tag_name)
  {
    $this->child = new self($tag_name, $this);
    return $this->child;
  }
  
  public function make($is_echo = false)
  {
    $child = &$this;
    while (1) {
      if ($child->child == null) break;
      $child = &$child->child; 
    }
    $current = &$child;
    do {
      if ($current->father == null) {
        $tag = self::get($current); break;
      }
      $current->father->contents(self::get($current));
      $current = &$current->father;
      if ($current->father != null) continue;
    } while(1);
    $this->init();
    if ($is_echo) echo $tag;
    return $tag;
  }
  
  private static function get(Tag $self)
  {
    $attributes = array();
    foreach ($self->attributes AS $k => $v) {
      if (is_null($v)) continue; 
      $attributes[] = "$k=\"$v\"";
    }
    $attributes = implode(" ", $attributes);
    if (in_array($self->tag_name, self::$no_content_tags)) {
      $tag = "<{$self->tag_name} $attributes/>";
    } else {
      $tag = "<{$self->tag_name} $attributes>{$self->contents}</{$self->tag_name}>";
    }
    return $tag . PHP_EOL;
  }
}