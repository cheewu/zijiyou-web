<div id="common-canvas" class="list poi">
<?=$this['multi']?>
  <table class="table table-bordered table-hover">
    <tr>
<?php
foreach ($this['fields'] AS $field) {
  if (!in_array($field, $this['sort_fields'])) {
    echo sprintf('<th class="list-%s">%s</th>', $field, $field);
  } else {
    if ($this['sort']['field'] != $field) {
      $icon = '';
      $order = '-';
    } else {
      $order = ($this['sort']['order'] == -1) ? '+' : '-';
      $icon  = ($this['sort']['order'] == -1) 
             ? '<i class="icon-arrow-down"></i>' 
             : '<i class="icon-arrow-up"></i>' ;
    }
    $href = Mf::$url->makeOrigin(array('sort' => "$order" . "$field", 'pg' => null));
    echo sprintf('<th class="list-%s"><a href="%s">%s%s</a></th>', 
                 $field, $href, $field, $icon);
  }
} 
?>
      <th class="list-link">Link</th>
      <th class="list-handle">Manage</th>
    </tr>
<?php 
foreach ($this['list'] AS $line) {
  echo "<tr>";
  foreach ($this['fields'] AS $field) {
    $class = "";
    $value = (isset($line[$field])) ? $line[$field] : "";
    switch ($field) {
      case 'area':
        $region = Mf::$mongo['tripfm.Region']->findOne(array('_id' => $line['regionId']),
                                                       array('name'));
        if ($this['rid'] == strval($line['regionId'])) {
          $value = $region['name']; break;
        }
        $value = sprintf('<a class="table-cell" href="%s">%s</a>', 
                   Mf::$url->makeOrigin(array('rid' => strval($line['regionId'])), MfUrl::Q_NEW),
                   $region['name']);
      break;
      case 'center':
        $class = "list-center";
        $value = !empty($value) ? '<i class="icon-ok"></i>' : '<i class="icon-remove"></i>';
      break;
      case 'rank':
        $class = "list-rank";
      default:
        if (is_array($value)) {
          $value = implode(" ", $value);
        }
        $value = sprintf('<div class="table-cell" title="%s">%s</div>', $value, $value);
      break;
    }
    echo sprintf('<td class="%s">%s</td>', $class, $value);
  }
  echo '<td class="list-link">';
  foreach (array('Region') AS $col) {
     $href =  Mf::$url->makeBasic("/list/" . strtolower($col), 
                                  array("rid" => strval($line['regionId'])));
     echo sprintf('<a href="%s" target="_blank" title="%s">%s</a>', $href, $col, substr($col, 0, 3));
  }
  echo <<<HTML
        </td>
        <td class="list-handle">
          <a href="/edit/poi/?id={$line['_id']}" target="_blank"><i class="icon-edit"></i></a>
          <span>&nbsp</span>
          <a href="#"><i class="icon-remove"></i></a>
        </td>
     </tr>
HTML;
}
?>
  </table>
</div>