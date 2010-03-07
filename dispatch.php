<?php
class Dispatcher {
	function dispatch () {
	  require_once 'framework.php';
    
		try {
			session_start();
			Framework::boot();
			Framework::$controller = new Controller;
      Framework::$controller->process_route();
      
		} catch (Exception $e) {
			Framework::$controller->process_exception($e);
		}
	}
	
}
?>