<?php

namespace Src\Controller;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Src\Model\UserModel;
use Src\Model\UserHistoryModel;

class UserController{
    
    private $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../View');
        $this->twig = new Environment($loader);
    }
    public function homeUser(){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $userType = $_SESSION['type'];
        $action = $_GET['action'] ?? '';
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'email_asc';
        if ($action==='reset'){
            $search='';
        }
        $error = $_SESSION['user_error'] ?? null;
        $succes = $_SESSION['user_succes'] ?? null;
        unset($_SESSION['user_error']);         
        unset($_SESSION['user_succes']);
        $userModel = new UserModel($pdo);
        $groups = $userModel->getGroup($_SESSION['id_user'], $search, $sort);
        $headers = ['email' => 'Email', 'first_name' => 'First name', 'last_name' => 'Last name', 'type' => 'Account type', 'actions' => ['label' => 'Actions', 'buttons' => [['/user/edit', 'Edit this user ?', 'Edit'], ['/product/delete', 'Delete this product ?', 'Delete']]]];
        $sortable_columns = $sortable_columns = ['email_asc' => 'Email A→Z', 'email_desc' => 'Email Z→A', 'last_name_asc' => 'Last name A→Z', 'last_name_desc' => 'Last name Z→A',
        'first_name_asc' => 'First name A→Z', 'first_name_desc'=> 'First name Z→A', 'type_asc' => 'Account type A→Z', 'type_desc' => 'Account type Z→A'];
        echo $this->twig->render('template_home.html.twig',[
            'title' => 'Manage users',
            'button_add' => ['+ User', 'user/add'],
            //'button_menu' => ['entry' => '+ Stock entry'],
            'search' => $search,
            'path' => '', 
            'sort' => $sort,
            'sortable_columns' => $sortable_columns,
            'subtitle' => 'RESULT',
            'headers' => $headers,
            'data' => $groups,
            'id' => 'id_user',
            'user_type' => $userType,
            'error' => $error,
            'succes' => $succes
        ]);

        
    }

    public function formAddUser(){
        $userType = $_SESSION['type'];
        $form_fields = [['name' => 'first_name', 'label' => 'First name :', 'type' => 'text', 'required' => true],
        ['name' => 'last_name', 'label' => 'Last name :', 'type' => 'text', 'required' => true],
        ['name' => 'email', 'label' => 'Email :', 'type' => 'email', 'required' => true ],
        ['name' => 'password', 'label' => 'Password :', 'type' => 'password', 'required' => true],
        ['name' => 'type', 'label' => 'Type de compte :', 'type' => 'select', 'required' => true, 'options' => ['manager' => 'Manager', 'user'    => 'User', 'guest'   => 'Guest']]];
        $error = $_SESSION['user_error'] ?? null;
        unset($_SESSION['user_error']);    
        echo $this->twig->render('template_form.html.twig', [
            'subtitle' => 'Add a user',
            'action' => '/user/add',
            'fields' => $form_fields,
            'submit_label' => 'Add',
            'user_type' => $userType,
            'error' => $error
        ]);

    }
    public function addUser(){
        require_once __DIR__ . '/../Model/UserModel.php';
        require_once __DIR__ . '/../Model/UserHistoryModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $userModel = new UserModel($pdo);
        $UserHistoryModel = new UserHistoryModel($pdo);
        $last_name  = trim($_POST['last_name'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $group = $_SESSION['id_user'] ?? null;
        if (empty($first_name) || !preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/", $first_name) || mb_strlen($first_name) > 50 ) {
            $_SESSION['user_error'] = "The first name is invalid.";
        }elseif (empty($last_name) || !preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/", $last_name) || mb_strlen($last_name) > 50) {
            $_SESSION['user_error'] = "The name is invalid.";
        }elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
            $_SESSION['user_error'] = "The email address is invalid.";
        }elseif (!($userModel->checkEmail($email))) {
            $_SESSION['user_error'] = "This email is already registered.";
        }elseif (empty($password) || !preg_match("/^[a-zA-Z0-9!@#$%^&*]{8,50}$/", $password)){
            $_SESSION['user_error'] = "The password is invalid.";
        }elseif (empty($type) || !in_array($type, ['admin', 'manager', 'user', 'guest'])){
            $_SESSION['user_error'] = "The account type is invalid.";
        }
        if (isset($_SESSION['user_error']) && !empty($_SESSION['user_error'])) {
            header('Location: /user/add');
            exit;
        }
        $userId = $userModel->addUser($email, $password, $first_name, $last_name, $type, $group);
        if (!$userId) {
            $_SESSION['user_error'] = "Failed to add the user.";
            header('Location: /user/add');
            exit;
        }
        $UserHistoryModel->addUserHistory($userId, $email, 'add', 1);
        $_SESSION['user_succes'] = "Succes to add the user.";
        header('Location: /user');
        exit;
    }

    public function formEditUser(){
        require_once __DIR__ . '/../Model/UserModel.php';
        require_once __DIR__ . '/../Model/MovementModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $id_user = $_POST['id_user'] ?? $_SESSION['id_variable'] ?? null;
        unset($_SESSION['id_variable']);
        $userModel = new UserModel($pdo);
        $user = $userModel->getUser($id_user);
        if (!$user) {
            $_SESSION['user_error'] = "Erreur modifier user";
            header('Location: /user');
            exit; 
        }
        $error = $_SESSION['product_error'] ?? null;
        unset($_SESSION['product_error']);
        $userType = $_SESSION['type'];
        $form_fields = [
        ['name' => 'id_user', 'type' => 'hidden', 'value' => $id_user],
        ['name' => 'first_name', 'label' => 'First name :', 'type' => 'text', 'required' => true, 'value' => $user[0]['first_name'] ?? ''],
        ['name' => 'last_name', 'label' => 'Last name :', 'type' => 'text', 'required' => true, 'value' => $user[0]['last_name'] ?? ''],
        ['name' => 'email', 'label' => 'Email :', 'type' => 'email', 'required' => true, 'value' => $user[0]['email'] ?? ''],
        ['name' => 'type', 'label' => 'Type de compte :', 'type' => 'select', 'required' => true, 'value' => $user[0]['type'] ?? '', 'options' => ['manager' => 'Manager', 'user'    => 'User', 'guest'   => 'Guest']]];
        $error = $_SESSION['user_error'] ?? null;
        unset($_SESSION['user_error']);    
        echo $this->twig->render('template_form.html.twig', [
            'subtitle' => 'Edit a user',
            'action' => '/user/edit',
            'fields' => $form_fields,
            'submit_label' => 'Edit',
            'user_type' => $userType,
            'error' => $error
        ]);


    }

    public function editUser(){
        require_once __DIR__ . '/../Model/UserModel.php';
        require_once __DIR__ . '/../Model/UserHistoryModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $userModel = new UserModel($pdo);
        $UserHistoryModel = new UserHistoryModel($pdo);
        $id_user = trim($_POST['id_user'] ?? '');
        $last_name  = trim($_POST['last_name'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $type = trim($_POST['type'] ?? '');
        if (empty($first_name) || !preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/", $first_name) || mb_strlen($first_name) > 50 ) {
            $_SESSION['user_error'] = "The first name is invalid.";
        }elseif (empty($last_name) || !preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/", $last_name) || mb_strlen($last_name) > 50) {
            $_SESSION['user_error'] = "The name is invalid.";
        }elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
            $_SESSION['user_error'] = "The email address is invalid.";
        }elseif (!($userModel->checkEmail($email))) {
            $_SESSION['user_error'] = "This email is already registered.";
        }elseif (!empty($password) && !preg_match("/^[a-zA-Z0-9!@#$%^&*]{8,50}$/", $password)){
            $_SESSION['user_error'] = "The password is invalid.";
        }elseif (empty($type) || !in_array($type, ['admin', 'manager', 'user', 'guest'])){
            $_SESSION['user_error'] = "The account type is invalid.";
        }elseif (empty($id_user)){
            $_SESSION['user_error'] = "The user is invalid.";
        }
        if (isset($_SESSION['user_error']) && !empty($_SESSION['user_error'])) {
            $_SESSION['id_variable']= $id_user;
            header("Location: /user/edit");
            exit;
        }
        $userId = $userModel->editUser($id_user, $email, $password, $first_name, $last_name, $type);
        if (!$userId) {
            $_SESSION['user_error'] = "Failed to edit the user.";
            header('Location: /user/edit');
            exit;
        }
        $UserHistoryModel->addUserHistory($id_user, $email, 'edit', 1);
        $_SESSION['user_succes'] = "Succes to edit the user.";
        header('Location: /user');
        exit;
    }

    public function deleteUser(){
        require_once __DIR__ . '/../Model/UserModel.php';
        require_once __DIR__ . '/../Model/MovementModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $id_user = $_POST['id_user'] ?? null;
        $userModel = new UserModel($pdo);
        $data_user = $userModel->getUser($id_user);
        $user  = $userModel->deleteUser($id_user);
        if ($user) {
            $succes = 1;
            $_SESSION['user_succes'] = "Succes user delete";
        } else {
            $succes = 0;
            $_SESSION['user_error'] = "Erreur supprimer user";
        }
        $productModel = new UserHistoryModel($pdo);
        $productModel->addUserHistory($id_user, $data_user[0]['email'], 'delete', $succes);
        header('Location: /user');
        exit;
    }

    

}
?>
