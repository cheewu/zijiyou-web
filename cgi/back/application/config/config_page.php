<?php
return array(
  /**
   * @var page_size
   */
  'defalut_size' => 20,

  /**
   * @var display
   */
  'default_display' => 5,

  /**
   * @var callback
   */
  'default_callback' => function ($display_slot, $cur_page, $total_page) {
    $multi = array();
    $page = new Tag('a');
    if ($cur_page > 1) {
      $page->attr('href', Mf::$url->makeOrigin(array('pg' => $cur_page - 1)))
           ->contents('Prev');
      $multi[] = sprintf('<li>%s</li>', $page->make());
    }
    if (reset($display_slot) != 1) {
      $page->attr('href', Mf::$url->makeOrigin(array('pg' => 1)))
           ->contents('1...');
      $multi[] = sprintf('<li>%s</li>', $page->make());
    }
    foreach ($display_slot AS $page_num) {
      $li = new Tag('li');
      $page->contents($page_num);
      if ($page_num != $cur_page) {
        $page->attr('href', Mf::$url->makeOrigin(array('pg' => $page_num)));
      } else{
        $li->attr('class', 'active');
        $page->attr('href', '#');
      }
      $li->contents($page->make());
      $multi[] = $li->make();
    }
    if (end($display_slot) != $total_page) {
      $page->attr('href', Mf::$url->makeOrigin(array('pg' => $total_page)))
           ->contents("...$total_page");
      $multi[] = sprintf('<li>%s</li>', $page->make());
    }
    if ($cur_page < $total_page) {
      $page->attr('href', Mf::$url->makeOrigin(array('pg' => $cur_page + 1)))
           ->contents('Next');
      $multi[] = sprintf('<li>%s</li>', $page->make());
    }
    return '<div class="pagination"><ul>' . implode(PHP_EOL, $multi) . '</ul></div>';
  }
);