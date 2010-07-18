<?php
/**
 * CRUD for Food
 * @package TianguisCabal
 * @author Roberto Villegas <ville1ero@gmail.com>
 */

//creamos la clase
class ProductsController extends Controller {

  //funcion publica que crea vista
  public function indexAction(){
	//creamos la vista
    $View = new View('products/list');
	//indicamos el titulo
    $View->assign('_title_', _('Productos'));
	//Category Model extiende del DAO carga de la base de datos la tabla products
    $Products = ProductsModel::getAll();
	//asignamos variable de campo y valor
    $View->assign('products', $Products);
	//Mostramos la vista
    $View->display();
  }
  
  public function viewAction(){
    $Request = Request::getInstance();
    
    $View = new View('products/view');
    $View->assign('_title_', _('Ver Productos'));
    
    $id = ( isset($Request->id) )?(int)$Request->id:0;
    
    $Food = new FoodModel($id);
    
    if ( !$Food->load() ) {
      $_SESSION['_MESSAGE_'] = _('That Category doesn\'t exists');
      header('Location: ' . BASE_URL . '/products');
      exit();
    }
    
    $View->assign('Products', $Products);
    $View->display();
  }
  
  public function editAction(){
    $Request = Request::getInstance();
    
    $View = new View('products/edit');
    $View->assign('_title_', _('Editar Productos'));
    
    $id = ( isset($Request->id) )?(int)$Request->id:0;
    $Products = new ProductsModel($id);
    $Products->load();
    
    $View->assign('Products', $Products);
    $View->display();
  }
  
  /**
   *
   * @return unknown_type
   * @todo $_POST shouldn't be used, check how this can be integrated with {@link Request}
   */
  public function saveAction(){
  	
    $View = new View('products/edit');
    $View->assign('_title_', _('Producto Guardado'));
    
    $id = ( isset($_POST['product_id']) )?(int)$_POST['product_id']:0;
    
    $Products = new ProductsModel($id);
    $Products->load();
    
    $Products->user_id = $_POST['user_id'];
    $Products->category_id=$_POST['category_id'];
    $Products->name=$_POST['name'];
    $Products->description=$_POST['description'];
    $Products->picture=$_POST['picture'];
    $Products->price=$_POST['price'];
    $Products->quantity=$_POST['quantity'];
    $Products->quality=$_POST['quality'];
    $Products->transaction=$_POST['transaction'];
    $Products->exchange_for=$_POST['exchange_for'];
    $Products->date=$_POST['date'];
    
    if ( !$Products->save() ) {
      $_SESSION['_MESSAGE_'] = _('El producto no pudo ser guardado');
      header('Location: ' . BASE_URL . '/products');
      exit();
    }
    
    $_SESSION['_MESSAGE_'] = _('Guardado');
    if ( $id == 0 ) {
      $DbConnection=DbConnection::getInstance();
      $id = $DbConnection->getLastId();
    }
    header('Location: ' . BASE_URL . "/products/view/?id=$id");
  }
  
  public function deleteAction() {
    $Request = Request::getInstance();
    $id = ( isset($Request->id) )?(int)$Request->id:0;
    
    $Products = new ProductsModel($id);
    
    if ( !$Products->load() ) {
      $_SESSION['_MESSAGE_'] = _('El producto no existe');
      header('Location: ' . BASE_URL . '/products');
      exit();
    }
    
    if ( !$Products->delete() ) {
      $_SESSION['_MESSAGE_'] = _('No se puede eliminar el producto');
      header('Location: ' . BASE_URL . '/products');
      exit();
    }
    
    $_SESSION['_MESSAGE_'] = _('Eliminado');
    header('Location: ' . BASE_URL . '/products');
    exit();
  }
}