<?php

namespace Src\Controller;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

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
        $succes=$_SESSION['login_succes'] ?? null;
        unset($_SESSION['login_succes']);
        $userType = $_SESSION['type'];
        echo $this->twig->render('home.html.twig', ['user_type' => $userType, 'succes' => $succes]);
    }

    

    

}
?>
