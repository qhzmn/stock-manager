<?php

namespace Src\Controller;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Src\Model\UserHistoryModel;
use Src\Model\UserModel;





class ConnectionController{
    private $twig;

    public function __construct(){
        $loader = new FilesystemLoader(__DIR__ . '/../View');
        $this->twig = new Environment($loader);
    }
    // Function to display the login form
     public function loginForm(){
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        echo $this->twig->render('login_form.html.twig', ['error' => $error]);
    }

    public function loginCheck(){
        require_once __DIR__ . '/../Model/UserHistoryModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $userModel = new UserModel($pdo);
        if (!isset($_POST['email'], $_POST['password'])) {
            $_SESSION['login_error'] = "Missing fields.";
             header('Location: ../');
            exit;
        }
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = "Empty fields are not permitted.";
            header('Location: /');
            exit;
        }
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu';
        $user = $userModel->login($email, $password);
        $id_user= $user['id_user'] ?? null;
        if($user){
            $state = 1;
            $_SESSION['login_succes'] = "Successful connection";
            session_regenerate_id(true);
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['type'] = $user['type'];
        }else{
            $state = 0;
            $_SESSION['login_error'] = "Connection failed";
        }
        $userHistoryModel = new UserHistoryModel($pdo);
        $userHistoryModel->addUserHistory($id_user, $email, "login", $state);
        header('Location: /');
        exit;
    }






    
    

    public function logout(){
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        header("Location: /");
        exit;


    }

    

    

}
?>
