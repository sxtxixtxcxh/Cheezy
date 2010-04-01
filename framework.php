<?php
 
class Framework {
  
  public static 
  	$controllers_path = null,
		$helpers_path = null,
		$models_path = null,
		$views_path = null,
		$layouts_path = null,
		$partials_path = null,
		$errors_path = null,
		$errors_default_path  = null,
    $views_extension = 'php',
    $current_controller_object = null,
    $controller = null,
    $cache = null,
    $prefs = '',
    $protected_method_prefix = '_',
    $tmp_path = '';
    
  function boot(){
    if ( !defined('APP_ROOT') ) {
      define('FRAMEWORK_ROOT', dirname(__FILE__));
      define('APP_ROOT', dirname(dirname(FRAMEWORK_ROOT)));
    }
    
    self::$tmp_path = APP_ROOT.'/tmp';

    
    self::$controllers_path     = APP_ROOT.'/controllers';
		self::$helpers_path         = APP_ROOT.'/helpers';
		self::$models_path          = APP_ROOT.'/models';
		self::$views_path           = APP_ROOT.'/_pages';
		self::$partials_path        = APP_ROOT.'/_partials';
		
		// set more paths
		self::$layouts_path         = APP_ROOT.'/_layouts';
		self::$errors_path          = APP_ROOT.'/_errors';
		self::$errors_default_path  = APP_ROOT.'/_errors/default';
		
    // initialize preference system
    include FRAMEWORK_ROOT.'/lib/preferences.php';
    self::$prefs = new PreferenceCollection( array(self::$tmp_path) );
    self::$prefs->read('cache', true, self::$tmp_path);
    self::$cache = self::$prefs->cache;
    Timer::start();
  }
  
}


class Controller {
	private $controller;
  private $action;
  private $action_params = array();
  private $content_for_layout = null;
  private $default_action = 'index';
	private $before_filters = array();
	private $after_filters = array();
	
  public $view_file;
  public $page;
  public $layout = '';
	public $controller_class;
  public $controller_object;
	
  
	function __construct () {}
	
  function render_partial($file, $locals = array()){
    $file = $this->partials_path.'/'.$file.'.'.Framework::$views_extension;
    $this->render_file($file, $locals);
  }
    
  function render_file($file, $locals = array()){
    
    // make sure the file exists
    if ( is_file($file) ) {
      
      // store each of the object's properties as vars accessible 
      // to the file withouth a "$this->" in front of it.
      if ( is_object($this) ) {
        foreach( $this as $tmp_key => $tmp_value ) {
          ${$tmp_key} = &$this->$tmp_key;
        }        
        unset($tmp_key, $tmp_value);
      }
      // store the content for layout if it's set...
      
      if ( $this->content_for_layout !== null ) {
        $content_for_layout = &$this->content_for_layout;
      }

      // store var in the locals array so it will be 
      // accessible in the file..
      if ( count($locals) ) {
        foreach( $locals as $tmp_key => $tmp_value ) {
          ${$tmp_key} = &$locals[$tmp_key];
        }
        unset($tmp_key, $tmp_value);
      }
      
      unset($locals);
      
      include( $file );

      return true;
      
    }else{
      
      // if the file doesn't exist... 
      return false;
      
    }
    
  }
  
  function render_page($view_file, $layout = 'default'){

    // render page, capturing output to send to layout...
    ob_start();
    
    // call default action/method if none is defined
		if ( $this->action === null ) {
			$this->action = $this->default_action;
		}

		// execute main method
		if ( $this->valid_action($this->action) ) {
			$action = $this->action;
			$this->controller_object->$action($this->action_params);
		} elseif ( is_file($this->views_path.'/'.$this->action.'.'.Framework::$views_extension) ) {
			$action = $this->action;
		} 
    
    // render the view
    if (!$this->render_file($view_file)){      
      $this->raise('Not Found', 'The requested URL '.$_SERVER['REQUEST_URI'].' was not found on this server.', 404);
    }
    
    // save the output into a variable...
    $this->content_for_layout = ob_get_contents();
    
    // and clean up.
    ob_end_clean();
    
    if ( Timer::$started ) ob_start();
    
    // check for the existence of an alternate layout...
    $layout_file = empty($this->layout) ? 
                    $this->layouts_path.'/'.$layout.'.'.Framework::$views_extension : 
                    $this->layouts_path.'/'.$this->layout.'.'.Framework::$views_extension;
    
    // if you opt out a layout or if the layout doesn't render
    // then just spit out the contents of the view
    if ( $this->layout === false || !$this->render_file($layout_file) ) {
      echo $this->content_for_layout;
    }
    
    if ( Timer::$started ) ob_end_flush();
    
  }
  
  function parse_request(){

    // should use a router, but this works for now.
    // do a little sanitation
    $this->page = str_replace('..', '', trim($_SERVER['REQUEST_URI'],'/'));
    
		$this->layouts_path = $this->layouts_base_path = Framework::$layouts_path;
		$this->views_path = Framework::$views_path;
		$this->partials_path = Framework::$partials_path;
    
    if(preg_match('/^admin/', $this->page)){
      $this->controller_class = 'AdminController';
  		$this->layouts_path = FRAMEWORK_ROOT.'/_layouts';
  		$this->views_path = FRAMEWORK_ROOT.'/_pages';
  		$this->partials_path = FRAMEWORK_ROOT.'/_partials';
      $this->page = $this->action = trim($this->page,'admin /');
    }
    if($this->page == '') $this->page = 'home';
    
    $this->view_file = $this->views_path.'/'.$this->page.'.'.Framework::$views_extension;
  }
  
  
  function process_route(){
    
    // let's figure out what view to use
    $this->parse_request();
    
		if ( class_exists($this->controller_class, true) ) {
		  $class = $this->controller_class;
		}else{
		  $class = 'ApplicationController';
		}
	  $this->controller_object = new $class();


		$this->controller_object->controllers_path  = &$this->controllers_path;
		$this->controller_object->views_path        = &$this->views_path;
		$this->controller_object->partials_path     = &$this->partials_path;
		$this->controller_object->layouts_path      = &$this->layouts_path;
		$this->controller_object->layouts_base_path = &$this->layouts_base_path;
		$this->controller_object->page              = &$this->page;

    // save a reference to the controller
    Framework::$current_controller_object = &$this->controller_object;
    
    // and render the page    
    return $this->render_page($this->view_file);
    
  }
  
  function valid_action ($action) {
		if ( $action !== null && substr($action, 0, 1) != Framework::$protected_method_prefix ) {

			// get all methods
			$all_methods = get_class_methods($this->controller_object); 

      // get inherited methods
      $inherited_methods = array_merge(
       get_class_methods(__CLASS__),
       $this->controller_object->before_filters,
       $this->controller_object->after_filters
      );
			
			if ( class_exists('ApplicationController', false) ) {
				$inherited_methods = array_merge($inherited_methods, get_class_methods('ApplicationController'));
			}

			// validate action
			$valid_actions = array_diff($all_methods, $inherited_methods);
			if ( in_array($action, $valid_actions) ) {
				return true;
			}
		}
		return false;
		
	}
	
  
  function raise ($message, $details, $code) {
    throw new FrameworkError($message, $details, $code);
  }
  
  function process_exception( $object ){
    
    $this->error = $object;
    $this->message = $object->getMessage();
    $this->details = $object->getDetails();
    $this->code = $object->getCode();
    $this->trace = $object->getTrace();

    if ( $this->code != 0 ) {
      header(StatusCode::http_header_for($this->code));
    }
    
    $paths = array(
      APP_ROOT.'/_errors',
      FRAMEWORK_ROOT.'/_errors',
    );
    
    foreach( $paths as $path ) {
      if ( is_file($path.'/'.$this->code.'.'.Framework::$views_extension) ) {
        $view_file = $path.'/'.$this->code.'.'.Framework::$views_extension;
        break;
      } elseif ( is_file($path.'/default.'.Framework::$views_extension) ) {
        $view_file = $path.'/default.'.Framework::$views_extension;
        break;
      }
    }
    
    if(StatusCode::can_has_body($this->code)){
      $this->render_file($view_file);
    }
    
  }
  
  

}


class FrameworkError extends Exception {
  
  protected $details = null;
  
  function __construct($message = null, $details = null, $code = 0) {
    $this->details = $details;
    parent::__construct($message, $code);
  }
  
  public function getDetails () {
    return $this->details;
  }

}


//
// Some global functions that can be used in the views
//

function render_partial(){
  $args = func_get_args();
  return call_user_func_array(array(Framework::$current_controller_object, 'render_partial'), $args);
}

function raise(){
  $args = func_get_args();
  return call_user_func_array(array(Framework::$current_controller_object, 'raise'), $args);
}

spl_autoload_register('framework_autoload');

function framework_autoload($class_name){

  // check cache if specified class has been found before
  if ( !empty(Framework::$cache->autoload[$class_name]) ) {
    if ( is_file(Framework::$cache->autoload[$class_name]) ) {
      include_once(Framework::$cache->autoload[$class_name]);
      $found = true;
    } else {
      unset(Framework::$cache->autoload[$class_name]);
      $save_cache = true;
    }
  }

  
  $autoload_paths  = array( APP_ROOT.'/lib/', 
                            FRAMEWORK_ROOT.'/lib/',
                            APP_ROOT.'/models/',
                            FRAMEWORK_ROOT.'/controllers/',
                          );

  $filename_styles = array( 'lower'=>strtolower($class_name),
                            'original'=>$class_name,
                          );

  $file_extensions = array( '.php', 
                            '.lib.php', 
                            '.class.php',
                          );

  if( empty($found)){
    foreach($autoload_paths as $path){
      foreach($filename_styles as $file){
        foreach($file_extensions as $extension){
          if( is_file($path.$file.$extension) ){
            Framework::$prefs->cache->autoload[$class_name] = $path.$file.$extension;
            $save_cache = true;
            include_once($path.$file.$extension);
            break 3;
          }
        }
      }
    }
  }
  
  if ( !empty($save_cache) ) {
    Framework::$prefs->cache->save();
  }
  
  
}

function getDirectoryTree( $outerDir , $x){ 
    $dirs = array_diff( scandir( $outerDir ), Array( ".", "..",".svn" ) ); 
    $dir_array = Array(); 
    foreach( $dirs as $d ){ 
        if( is_dir($outerDir."/".$d)  ){ 
            $dir_array[ $d ] = getDirectoryTree( $outerDir."/".$d , $x); 
        }else{ 
         if (($x)?preg_match('/'.$x.'$/',$d):1) 
            $dir_array[ $d ] = $d; 
            } 
    } 
    return $dir_array; 
}

?>