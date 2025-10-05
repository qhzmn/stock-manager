<?php

namespace Src\Controller;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Src\Model\UserModel;
use Src\Model\UserHistoryModel;


class HomeController
{
    private $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../View');
        $this->twig = new Environment($loader);
    }
    

    public function home()
    {
        $succes=$_SESSION['login_succes'] ?? $_SESSION['profil_succes'] ?? null;
        unset($_SESSION['login_succes']);
        unset($_SESSION['profil_succes']);
        $userType = $_SESSION['type'];
      
        $currentPath = $_SERVER['REQUEST_URI'];
        echo $this->twig->render('home.html.twig', ['currentPath' => $currentPath, 'user_type' => $userType, 'succes' => $succes]);
    }

    public function formEditProfil(){
        require_once __DIR__ . '/../Model/UserModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $id_user = $_SESSION['id_user'];
        $userModel = new UserModel($pdo);
        $user = $userModel->getUser($id_user);
        $error = $_SESSION['profil_error'] ?? null;
        unset($_SESSION['profil_error']);
        $currentPath = $_SERVER['REQUEST_URI'];
        $form_fields = [['name' => 'id_user', 'type' => 'hidden', 'value' => $user[0]['id_user'] ?? ''],
        ['name' => 'first_name', 'label' => 'First name :', 'type' => 'text', 'required' => true, 'value' => $user[0]['first_name'] ?? ''],
        ['name' => 'last_name', 'label' => 'Last name :', 'type' => 'text', 'required' => true, 'value' => $user[0]['last_name'] ?? ''],
        ['name' => 'email', 'label' => 'Email :', 'type' => 'text', 'required' => true, 'value' => $user[0]['email'] ?? ''],
        ['name' => 'current_password', 'label' => 'Current password :', 'type' => 'password', 'required' => false],
        ['name' => 'new_password1', 'label' => 'New password :', 'type' => 'password', 'required' => false],
        ['name' => 'new_password2', 'label' => 'Re-enter the new password :', 'type' => 'password', 'required' => false]];
        echo $this->twig->render('template_form.html.twig', ['currentPath' => $currentPath,
            'subtitle' => 'Edit profil',
            'action' => '/profil',
            'fields' => $form_fields,
            'submit_label' => 'Edit',
            //'user_type' => $userType,
            'error' => $error
        ]);
        
    }
    public function editProfil() {
        require_once __DIR__ . '/../Model/UserModel.php';
        require_once __DIR__ . '/../Model/UserHistoryModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $userModel = new UserModel($pdo);
        $userHistoryModel = new UserHistoryModel($pdo);
        $id_user = $_SESSION['id_user'];
        $email = trim($_POST['email'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $current_pass = trim($_POST['current_password'] ?? '');
        $new_pass1 = trim($_POST['new_password1'] ?? '');
        $new_pass2 = trim($_POST['new_password2'] ?? '');
        if (empty($first_name) || !preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/", $first_name) || mb_strlen($first_name) > 50 ) {
            $_SESSION['profil_error'] = "The first name is invalid.";
        }
        if ($userModel->checkEmail($email)) {
            $$_SESSION['profil_error'] = "This email is already registered.";
        }
        if (empty($last_name) || !preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/", $last_name) || mb_strlen($last_name) > 50) {
            $_SESSION['profil_error'] = "The name is invalid.";
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
            $_SESSION['profil_error'] = "The email address is invalid.";
        }
        if ($current_pass || $new_pass1 || $new_pass2) {
            if (empty($current_pass) || empty($new_pass1) || empty($new_pass2)) {
                $_SESSION['profil_error'] = "All password fields must be completed.";   
            } elseif ($new_pass1 !== $new_pass2) {
                $_SESSION['profil_error'] = "The new passwords do not match.";
            } elseif (50 < mb_strlen($new_pass1) || mb_strlen($new_pass1) < 8) {
                $_SESSION['profil_error'] = "The new password must contain between 8 and 50 characters.";
            } elseif (!($userModel->login($email, $current_pass))){
                $_SESSION['profil_error'] = "The current password is invalid.";
            }
        }
        if (isset($_SESSION['profil_error']) && !empty($_SESSION['profil_error'])) {
            header('Location: /profil');
            exit;
        }
        $user = $userModel->editUser($id_user, $email, $new_pass1, $first_name, $last_name, $_SESSION['type']);
        if ($user){
            $userHistoryModel->addUserHistory($id_user, $email, 'edit', 1);
            $_SESSION['profil_succes'] = "Success: profile modified";
        }else{
            $userHistoryModel->addUserHistory($id_user, $email, 'edit', 0);
            $_SESSION['profil_error'] = "Error: profile modified";
            header('Location: /profil');
            exit;
        } 
        header('Location: /');
        exit;
    }

    public function policies() {
        $currentPath = $_SERVER['REQUEST_URI'];
        echo $this->twig->render('template_legal.html.twig', ['currentPath' => $currentPath, 'page_type' => 'privacy']);
    }
    public function notices() {
        $currentPath = $_SERVER['REQUEST_URI'];
        echo $this->twig->render('template_legal.html.twig', ['currentPath' => $currentPath, 'page_type' => 'legal']);
    }

    

    

}
?>
