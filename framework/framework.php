<?php
 
class Framework {
  
  public static 
    $views_extension = 'php',
    $current_controller_object = null,
    $controller = null,
    $cache = null,
    $prefs = '',
    $tmp_path = '';
    
  function boot(){
    if ( !defined('APP_ROOT') ) {
      define('APP_ROOT', dirname(dirname(__FILE__)));
    }
    self::$tmp_path = APP_ROOT.'/tmp';

    // initialize preference system
    include APP_ROOT.'/framework/lib/preferences.php';
    self::$prefs = new PreferenceCollection( array(self::$tmp_path) );
    self::$prefs->read('cache', true, self::$tmp_path);
    self::$cache = self::$prefs->cache;
    Timer::start();
  }
  
}


class Controller {
  private $content_for_layout = null;
  public $view_file;
  public $layout = '';
  
  function render_partial($file, $locals = array()){
    $file = APP_ROOT.'/_partials/'.$file.'.'.Framework::$views_extension;
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
                    APP_ROOT.'/_layouts/'.$layout.'.'.Framework::$views_extension : 
                    APP_ROOT.'/_layouts/'.$this->layout.'.'.Framework::$views_extension;
    
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
    $view = str_replace('..', '', trim($_SERVER['REQUEST_URI'],'/'));
    if($view == '') $view = 'home';
    $this->view_file = APP_ROOT.'/_pages/'.$view.'.'.Framework::$views_extension;

  }
  
  
  function process_route(){
    
    // let's figure out what view to use
    $this->parse_request();
    
    // save a reference to the controller
    Framework::$current_controller_object = &$this;
    
    // and render the page
    return $this->render_page($this->view_file);
    
  }
  
  function raise ($message, $details, $code) {
    throw new FrameworkError($message, $details, $code);
  }
  
  function process_exception( $object ){
    echo '<pre style="clear:left;text-align:left">';
    var_dump($object);
    echo '</pre>';
    die(__FILE__ .' <br /> #: '. __LINE__);
    
    $this->error = $object;
    $this->message = $object->getMessage();
    $this->details = $object->getDetails();
    $this->code = $object->getCode();
    $this->trace = $object->getTrace();

    if ( $this->code != 0 ) {
      header(StatusCode::http_header_for($this->code));
    }
    
    $paths = array(
      '_errors',
      'framework/_errors',
    );
    
    foreach( $paths as $path ) {
      if ( is_file(APP_ROOT.'/'.$path.'/'.$this->code.'.'.Framework::$views_extension) ) {
        $view_file = APP_ROOT.'/'.$path.'/'.$this->code.'.'.Framework::$views_extension;
        break;
      } elseif ( is_file(APP_ROOT.'/'.$path.'/default.'.Framework::$views_extension) ) {
        $view_file = APP_ROOT.'/'.$path.'/default.'.Framework::$views_extension;
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
                            APP_ROOT.'/framework/lib/',
                            APP_ROOT.'/models/',
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
          echo '<pre style="clear:left;text-align:left">';
          var_dump($path.$file.$extension);
          echo '</pre>';


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

?>