<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Example<?php if(isset($title)) echo ' - '. $title;?></title>
</head>

<body>
<?php render_partial('header'); ?>

<?php echo $content_for_layout; ?>

<?php if (Timer::$started): ?>
<div id="timer">
  <?php echo Timer::end(5);?>
  <?php echo " - " .round(memory_get_usage(true) / (1024*1024),3)." MB";?>
</div>
<?php endif; ?>
</body>
</html>