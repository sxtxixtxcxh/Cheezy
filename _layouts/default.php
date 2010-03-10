<!DOCTYPE html>
<html>
<head>
  <title>Admin</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <link rel="stylesheet" href="/stylesheets/admin/screen.css" type="text/css" media="screen" title="no title" charset="utf-8">
</head>
<body>
  <div id="container">
    <div id="header">
      <?php render_partial('header'); ?>
    </div>
    <div id="content">
      <?php echo $content_for_layout; ?>
    </div>
    <div id="sidebar">
      <?php render_partial('menu'); ?>
    </div>
    <?php if (Timer::$started): ?>
    <div id="footer">
      A Prototype App &copy; 2010 
      <div id="timer">
        <?php echo Timer::end(5);?>
        <?php echo " - " .round(memory_get_usage(true) / (1024*1024),3)." MB";?>
      </div>
    </div>
    <?php endif; ?>
  </div>

</body>
</html>
