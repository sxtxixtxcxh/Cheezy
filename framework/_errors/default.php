<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<title><?php echo StatusCode::http_header_for($code) ?></title>
	
	<style type="text/css" media="screen">
		BODY {
			font-family: verdana, tahoma, arial, helvetica, sans-serif;
			font-size: 12px;
			margin: 0px;
			padding: 16px;
		}
		
		CODE {
			margin: 0px;
			padding: 0px;
		}
		
		A {
			color: #000;
		}
		
		A:hover {
			background-color: #F5F5F5;
		}
		
		h1 {
			font-size: 22px;
			margin: 10px 5px 12px 5px;
		}

		#details {
			background-color: #EEE;
			font-style: italic;
			padding: 14px;
			margin: 0px;
		}
		#server_info{
		  font-style: italic;
			padding: 14px;
			margin: 0px;
			font-size: 9px;
		}
	</style>
	
</head>

<body>

<?php if( !empty($message) ): ?>
  <h1><?php echo $message; ?></h1>
<?php endif;?>

<?php if( !empty($details) ): ?>
  <div id="details">
  	<?php echo $details; ?>
  </div>
<?php endif;?>

<div id="server_info">
  <?php echo $_SERVER['SERVER_SOFTWARE'].' Server at '.$_SERVER['HTTP_HOST'].' Port '.$_SERVER['SERVER_PORT']; ?>
</div>

</body>
</html>
