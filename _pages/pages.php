<h1><?php echo $title; ?></h1>
<?php echo '<pre style="clear:left;text-align:left">';
var_dump($this);
echo '</pre>';
die(__FILE__ .' <br /> #: '. __LINE__);
 ?>
<ul id="pages">
  <?php foreach($pages as $key => $page):?>
  <li><?php echo $page; ?></li>
  <?php endforeach;?>
</ul>