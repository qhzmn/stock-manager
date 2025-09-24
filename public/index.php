<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Src\Controller\HomeController;
use Src\Controller\ConnectionController;
use Src\Controller\ProductController;
use Src\Controller\UserController;
use Src\Controller\StockController;


$login = isset($_SESSION['id_user']);
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (!$login){
    $uri = '/login';  
}

switch ($uri) {
    case '/':
        $controller = new HomeController();
        $controller->home();
        break;

    case '/login':
        $controller = new ConnectionController();
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method==='POST'){
            $controller->loginCheck();
        }else{
            $controller->loginForm();
        }
        break;
    
    case '/product':
        $controller = new ProductController();
        $controller->homeProduct();
        break;
    case '/product/add':
        $controller = new ProductController();
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method==='POST'){
            $controller->addProduct();
        }else{
            $controller->formAddProduct();
        }
        break;
    case '/product/edit':
        $controller = new ProductController();
        if ($_SERVER['REQUEST_METHOD']==='POST'){
            if (count($_POST) === 1 && isset($_POST['id_product'])) {
                $controller->formEditProduct();
            } elseif (count($_POST) > 1) {
                $controller->editProduct();
            }   
        }
        break;
    case '/product/delete':
        $controller = new ProductController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->deleteProduct();
        }
        break;
    case '/stock':
        $controller = new StockController();
        $controller->homeStock();
        break;
    case '/stock/entry':
        $controller = new StockController();
        $controller->movementStock('entry');
        break;
    case '/stock/entry/add':
        $controller = new StockController();
        $controller->addMovementStock('entry');
        break;
    case '/stock/exit':
        $controller = new StockController();
        $controller->movementStock('exit');
        break;
        
    case '/stock/exit/add':
        $controller = new StockController();
        $controller->addMovementStock('exit');
        break;


























    case '/logout':
        //$controller = new ConnectionController();
        //$controller->logout();
        break;
    case '/profil':
        //$controller = new ConnectionController();
        //$controller->profil();
        break;
    
    
    case '/product/select':
        $controller = new ProductController();
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method==='POST'){
            $controller->selectProduct();
        }else{
            $controller->formSelectProduct();
        }
        break;
        
    
    
    
    case '/product/stats':
        
        $controller = new ProductController();
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method==='GET'){            
            $controller->statProduct($_GET['id_product']);
        }

    
    
    
        
    



    case '/stock/movement':
        $controller = new StockController();
        $controller->homeMovement();
        break;

         
    case '/stock/alert':
        $controller = new StockController();
        $controller->homeAlert();
        break;
    case '/stock/alert/add':
        $controller = new StockController();
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method==='POST'){
            $controller->addAlert();
        }else{
            $controller->formAddAlert();
        }
        break;
    case '/stock/alert/delete':
        $controller = new StockController();
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method==='POST'){
            $controller->deleteAlert();
        }
        break;
    case '/users':
        $controller = new UserController();
        $controller->homeUser();
        break;

    default:
        http_response_code(404);
        echo "Page non trouvée";
        echo ($uri);
        break;
}

?>

