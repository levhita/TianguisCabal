<?php
/**
 * Include files based on the Instance's ClassName being created
 *
 * Gets {@link Controller} from application/controllers/
 * Gets {@link View}s from application/views/
 * Gets {@link Model}s from application/models/
 * Every other class it gets it from application/core/
 *
 * For classes that doesn't form part of the core, you must include the file
 * by hand.
 *
 * @package Garson
 */
class Autoloader {

  public static function autoload($class_name)
  {
    $class_name = ucwords($class_name);
    
    /** Application Specific Classes are inside this directories **/
    $named_directories = array (
        'Controller' => 'controllers/',
        'View' => 'views/',
        'Model' => 'models/',
    );

    $is_core = true;

    foreach ( $named_directories AS $name => $directory ) {
        if ( stristr( $class_name, $name ) && $class_name != $name ) {
            $path = $directory . $class_name;
            $is_core = false;
            break;
        }
    }

    /** All other classes are inside the core **/
    if ( $is_core ) {
        $path = '../lib/core/' . $class_name;
    }
   
    /** add the application path and the php extension **/
    if ( !file_exists(APPLICATION_PATH . '/' . $path . '.php') ) {
      return false;
    }
    require_once APPLICATION_PATH . '/' . $path . '.php';
    return true;
  }
  
  /**
   * Configure autoloading using Core
   *
   * This is designed to play nicely with other autoloaders.
   */
  public static function registerAutoload()
  {
    spl_autoload_register(array('Autoloader', 'autoload'));
  }
}