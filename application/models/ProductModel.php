<?php
/**
 * Extends DAO to provide Category Specific functionality
 * @author Argel Arias <levhita@gmail.com>
 * @package TianguisCabal
 */
class ProductModel extends DAO {
  
  public function __construct($id){
    parent::__construct('product', (int)$id);
    parent::setIdField('product_id');
  }
  /**
   * Gets All Categories
   * @return array
   */
  public static function getAll()
  {
    $sql = "SELECT * FROM product";
    $DbConnection = DbConnection::getInstance();
    return $DbConnection->getAll($sql);
  }
}