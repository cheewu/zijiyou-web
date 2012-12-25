<div id="common-canvas" class="list subway">
<?=$this['multi']?>
  <table class="table table-bordered table-hover">
    <tr>
      <?php include 'thead.php';?>
      <th class="list-handle">Manage</th>
    </tr>
<?php 
foreach ($this['list'] AS $line) {
  echo "<tr>";
  $region = Mf::$mongo['tripfm.Region']->findOne(array('_id' => $line['regionId']));
  foreach ($this['fields'] AS $field) {
    $class = "";
    $value = (isset($line[$field])) ? $line[$field] : "";
    switch ($field) {
      case 'regionName':
        $value = $region['name'];
      break;
      case 'link':
        $class = 'list-link';
        $value = $line['wiki'];
        $value = sprintf('<a href="%s" title="wikipedia">wiki</a>', $value);
      break;
      default: 
        $value = sprintf('<div class="table-cell" title="%s">%s</div>', $value, $value);  
      break;
    }
    echo sprintf('<td class="%s">%s</td>', $class, $value);
  }
  echo '<td class="list-handle">
          <a href="/edit/subway/?id=' . strval($line['_id']) . '"><i class="icon-edit"></i></a>
          <span>&nbsp</span>
          <a href="#"><i class="icon-remove"></i></a>
        </td>';
  echo '</tr>';
}
?>
  </table>
</div>