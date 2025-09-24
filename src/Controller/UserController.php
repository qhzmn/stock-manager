<?php

namespace Src\Controller;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Src\Model\UserModel;

class UserController
{
    private $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../View');
        $this->twig = new Environment($loader);
    }
    public function homeUser(){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $userModel = new UserModel($pdo);
        $groups = $userModel->getGroup($_SESSION['id_user']);
        echo $this->twig->render('home_users.html.twig', ['groups' => $groups]);
    }


    public function viewUser(){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $userModel = new UserModel($pdo);
        $id = $_SESSION['id_user'];
        $comptes = $userModel->getUser($id);
        $boucle = $comptes;
        foreach ($boucle as $compte) {
            $comptes = $comptes + $userModel->getUser($compte['id_user']);
        }
        var_dump($comptes);
        $userType = $_SESSION['type'];
        echo $this->twig->render('user.html.twig', ['user_type' => $userType, 'comptes' => $compte]);
    }

    public function viewAddUser(){
        $userType = $_SESSION['type'];
        echo $this->twig->render('adduser.html.twig', ['user_type' => $userType]);

    }
    public function registerAddUser(){
        require_once __DIR__ . '/../Model/UserModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $userModel = new UserModel($pdo);
        $email = $_POST['email'];
        $password = $_POST['password'];
        $typecompte = $_POST['type'];
        $group = $_SESSION['id_user'];
        $success = $userModel->addUser($email, $password, $typecompte, $group);
        if ($success) {
        header('Location: /users?success=deleted');
        } else {
        header('Location: /users?error=not_found');
        }
        exit; 

        
    }

    


}
?>
