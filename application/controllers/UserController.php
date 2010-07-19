<?php
/**
 * Handles everything about the user
 * @package TianguisCabal
 */
class UserController extends controller
{
  
  public function indexAction()
  {
    $View = new View('user/index');
    $View->assign('_title_', _('Login'));
    $View->display();
  }
  
  public function loginAction()
  {
    if ( empty($_POST['name']) || empty($_POST['password']) ) {
      $this->gotoPage('/user/index', 'User or Password is Empty');
    }
    
    $name = $_POST['name'];
    $password = $_POST['password'];
    
    if( !$User = UserModel::getByName($name) ) {
      $this->gotoPage('/user/index', 'That user doesn\'t exists');
    }
    
    $User->load();
    
    if ( !$User->validatePassword($password) ) {
      $this->gotoPage('/user/index', 'The password is wrong');
    }
    
    if ( $User->role=='new') {
      $this->gotoPage('/user/index', 'You must validate your email account first.');
    }
    //@todo Create a {@link Session} object to handle session stuff
    $_SESSION['user_id'] = $User->getId();
    $this->gotoPage("/product", "You had logged in as ". htmlspecialchars($User->name));
  }

  
  public function viewAction() {
    $View = new View('user/view');
    $View->assign('_title_', _('User Detail'));
    $Request = Request::getInstance();
    $user_id = $Request->user_id;
    
    $User = new UserModel((int)$user_id);
    $User->load();
    
    $edit_link = false;
    if ( $LoggedUser = UserModel::getLoggedInUser() ) {
      if ( $LoggedUser->role == 'admin' ) {
        $edit_link=true;
      } else if ( $LoggedUser->user_id == $user_id ) {
        $edit_link=true;
      }
    }
    $View->assign('edit_link', $edit_link);
    $View->assign('User', $User);
    
    $View->display();
  }
  
  public function logoutAction() {
    UserModel::logout();
    $this->gotoPage('/index', 'Succefully logged out');
  }

  public function newAction() {
    if ( $User = UserModel::getLoggedInUser() ) {
      $this->gotoPage('/index', 'You can\'t register being logged in');
    }
    $View= new View('user/new');
    $View->assign('_title_', _('New User') );
    $View->display();
  }
  
  /**
   * @return null
   */
  public function saveAction() {
    
    if ( !$user_id = $_POST['user_id'] ) {
      self::gotoPage('/product', 'Mmmmm');
    }
    
    if( !$LoggedUser = UserModel::getLoggedInUser() ) {
      self::gotoPage('/', _('You Haven\'t started a session.'));
    }
    
    if ( $LoggedUser->getId() != $user_id ) {
      if ( $LoggedUser->role != 'admin' ) {
        self::gotoPage('/', _('You don\'t have permission to do that.')); 
      }
    }

    $User = new UserModel((int)$user_id);
    
    /** 
     * To overwrite the password the user must set both password and
     * repeat_passwor be the same, and the user be an admin or saving himself
     */
    if (!empty($_POST['password']) && !empty($_POST['repeat_password']) ) {
      if ( $_POST['passsword'] != $_POST['repeat_passsword'] ) {
        self::gotoPage("user/edit/?user_id=$user_id", _('The password doesn\t match') );
      }
      $User->$password = sha1($_POST['password']);
    }
    
    /** Only Admins can modify the role **/
    if ( $LoggedUser->role == 'admin' ) {
      $User->role = $_POST['role'];
    }
       
    $User->name       = $_POST['name'];
    $User->full_name  = $_POST['full_name'];
    $User->email      = $_POST['email'];
    $User->description= $_POST['description'];
    $User->twitter    = $_POST['twitter'];
    
    try {
      $User->save();
    } catch(Exception $e) {
      self::gotoPage("/user/edit/?user_id=$user_id", _('Couldn\' save the user') );
    }
    
    self::gotoPage("/user/view/?user_id=$user_id", _('User Saved') );
  }
  
  public function registerAction() {
    $name = $_POST['name'];
    if ( UserModel::getByName($name) ) {
      $this->gotoPage('/user/new', 'That username is already taken');
    }
    
    $User             = new UserModel(0);
    $User->name       = $name;
    $User->password   = sha1($_POST['password']);
    $User->full_name  = $_POST['full_name'];
    $User->email      = $_POST['email'];
    $User->date       = date("Y-m-d");
    
    $User->save();
    
    $validation_url= BASE_URL."/user/validate/?user_id=" . $User->getId() .
    "&code=".rawurlencode($User->generateCode());
    
    /* Creates the email's body from a template */
    $body = file_get_contents('../application/email_template.txt');
    $search = array ('{validation_url}', '{name}', '{full_name}', '{base_url}');
    $replace = array ($validation_url, $User->name, $User->full_name, BASE_URL);
    $body = str_replace($search, $replace, $body);
    
    $Mailer = new Mailer();
    $Mailer->send($User->email, $User->full_name, 'Please validate your email account', $body);
    
    $this->gotoPage("/user/view/?user_id=$User->user_id", "User saved as: ". htmlspecialchars($User->name));
  }
  
  public function validateAction() {
    $Request = Request::getInstance();
    $user_id = $Request->user_id;
    $given_code = rawurldecode($Request->code);
    
    $User = new UserModel((int)$user_id);
    $User->load();
    
    if ( !$User->validateCode($given_code) ) {
      $this->gotoPage('/user', 'The validation code is invalid!');
    }
    $User->role='registered';
    $User->save();
    $this->gotoPage("/user", "Congratulations, now you are fully registered!<br/>please login");
  }
  
  public function editAction() {
    $Request = Request::getInstance();
    $user_id = $Request->user_id;

    $User = new UserModel((int)$user_id);
    $User->load();
   
    $LoggedUser = UserModel::getLoggedInUser();
    $role_field = false;
    if($LoggedUser->role == 'admin' ){
      $role_field = true;
    }
    $View= new View('user/edit');
    
    $View->assign('_title_', _('Edit User') );
    $View->assign('User', $User);
    $View->assign('role_field', $role_field);
    
    $View->display();
  }
}
