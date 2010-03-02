<?php
/*

   Zynapse Preference Class

   This class is used to store preferences, it is humanly editable
   and requires no overhead processing to be loaded.

*/

class cache_preferences extends PreferenceContainer {
	
	public $autoload;
	
	function __construct () {
		parent::__construct();
		
		$this->autoload = array(
			'Timer' => "/Users/dave/Sites/dev/example/framework/lib/timer.lib.php",
			'StatusCode' => "/Users/dave/Sites/dev/example/framework/lib/statuscode.class.php",
		);
	}
	
}

?>