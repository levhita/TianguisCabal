<?php
/**
 * Holds {@link DbConnection} class
 * @package Garson
 * @author Argel Arias <levhita@gmail.com>
 * @copyright Copyright (c) 2007, Argel Arias <levhita@gmail.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
  * Database Connection abstraction
  *
  * Provides extremely useful functions for data retrieval, and other database
  * affairs.
  * @package Garson
  */
class DbConnection {
  
  /**
   * Holds the instance of the singleton
   * @var DbConnection
   */
  protected static $__instance = null;
  /**
   * Holds the MySQL Link to the Database
   * @var resource
   */
  protected $db_connection  = null;
  /**
   * An array with all the errors
   * @var array
   */
  protected $errors      = array();
  
  protected function __construct()
  {
    require_once "application/models/Config.inc.php";
    if ( !$this->db_connection = @mysql_connect(Config::getConfig('db_host'), Config::getConfig('db_user'), Config::getConfig('db_password')) ) {
      throw new RunTimeException("Couldn't connect to the database server");
    }
    if ( !@mysql_select_db(Config::getConfig('db_name'), $this->db_connection) ) {
      throw new RunTimeException("Couldn't connect to the given database");
    }
  }
  
  /**
   * Get a single instance of the class (Singleton)
   * @return DbConnection
   */
  public static function getInstance() {
    if (!self::$__instance instanceof self) {
      self::$__instance = new self;
    }
    return self::$__instance;
  }
  
  /**
   * Gets an Multidimensional associative array, with all the results:
   * Array(
   *  1=> array( id=1, nick=levhita, name=argel, lastname=arias)
   *  2=> array( id=32, nick=renich, name=renich, lastname=bon)
   *  3=> array( id=5, nick=b3t0, name=b3, lastname=t0)
   *  )
   * @param string $sql
   * @return array
   */
  public function getAllRows($sql)
  {
    if ( !$results = @mysql_query($sql, $this->db_connection) ) {
      throw new RunTimeException("Couldn't execute query: ". mysql_error($this->db_connection) );
    }
    
    $count = 0;
    $rows  = array();
    while ( $row = mysql_fetch_assoc($results) ) {
      $rows[] = $row;
      $count++;
    }
    return ($count)?$rows:false;
  }
  
  /**
   * Gets one array with the results in one column:
   * Array (
   *   1=levhita
   *   2=renich
   *   3=b3t0
   * )
   * @param string $sql
   * @return unknown_type
   */
  public function getOneColumn($sql)
  {
    if ( !$results = @mysql_query($sql, $this->db_connection) ) {
      throw new RunTimeException("Couldn't execute query: ". mysql_error($this->db_connection) );
    }
    
    $count = 0;
    $rows  = array();
    while ( $row = mysql_fetch_array($results) ) {
      $rows[] = $row[0];
      $count++;
    }
    return ($count)?$rows:false;
  }
  
  /**
   * Gets an array as a key => value pair:
   * Array (
   *   1=levhita
   *   32=renich
   *   5=b3t0
   * )
   * @param string $sql
   * @return array
   */
  public function getArrayPair($sql)
  {
    if ( !$results = @mysql_query($sql, $this->db_connection) ) {
      throw new RunTimeException("Couldn't execute query: ". mysql_error($this->db_connection) );
    }
    
    $count = 0;
    $rows  = array();
    while ( $row = mysql_fetch_array($results) ) {
      $rows[$row[0]] = $row[1];
      $count++;
    }
    return ($count)?$rows:false;
  }
  
  /**
   * Gets only the first Row as an associative array:
   * Array (
   *   id=1
   *   nick=levhita
   *   name=argel
   *   lastname=arias
   * )
   * @param string $sql
   * @return array
   */
  public function getOneRow($sql)
  {
    if ( !$results = @mysql_query($sql, $this->db_connection) ) {
      throw new RunTimeException("Couldn't execute query: ". mysql_error($this->db_connection) );
    }
    
    if ( $row = mysql_fetch_assoc($results) ) {
      return $row;
    }
    return false;
  }
  
  /**
   * Gets only the first value of the first row
   * @param string $sql
   * @return string
   */
  public function getOneValue($sql)
  {
    if ( !$results = @mysql_query($sql, $this->db_connection) ) {
      throw new RunTimeException("Couldn't execute query: ". mysql_error($this->db_connection) );
    }
    
    if ( $row = mysql_fetch_array($results) ) {
      return $row[0];
    }
    return false;
  }
  
  /**
   * Executes a query directly
   * @param string $sql
   * @return boolean
   */
  public function executeQuery($sql)
  {
    if ( !@mysql_query($sql, $this->db_connection) ) {
      throw new RunTimeException("Couldn't execute query: ". mysql_error($this->db_connection) );
    }
    return true;
  }
  
  /**
   * Used when you need to know the id of the row that you just inserted
   * @return integer
   */
  public function getLastId()
  {
    return mysql_insert_id($this->db_connection);
  }
}