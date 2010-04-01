<?php
class AdminController extends Controller {
	function __construct () {
		parent::__construct();
	}
	
	function index()
	{
	 
	}
	
	function pages()
	{	  
    $this->pages = getDirectoryTree(APP_ROOT.'/_pages');
	}
}
?>