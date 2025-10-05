

<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Src\Controller\HomeController;
use Src\Controller\ConnectionController;
use Src\Controller\ProductController;
use Src\Controller\UserController;
use Src\Controller\StockController;
use Src\Controller\StatisticController;


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
    case '/profil':
        $controller = new HomeController();
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method==='POST'){
            $controller->editProfil();
        }else{
            $controller->formEditProfil();
        }
        break;
    case '/notices':
        $controller = new HomeController();
        $controller->notices();
        break;

    case '/policies':
        $controller = new HomeController();
        $controller->policies();
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
    case '/logout':
        $controller = new ConnectionController();
        $controller->logout();
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
    case '/stock/movement':
        $controller = new StockController();
        $controller->homeMovement();
        break;


    case '/user':
        $controller = new UserController();
        $controller->homeUser();
        break;
    case '/user/add':
        $controller = new UserController();
        if ($_SERVER['REQUEST_METHOD']==='POST'){
            $controller->addUser();
        }else{
            $controller->formAddUser();
        }
        break;
    case '/user/edit':
        $controller = new UserController();
        if ($_SERVER['REQUEST_METHOD']==='POST' && count($_POST) != 1){
            $controller->editUser();
        }else{
            $controller->formEditUser();
        }    
        break;
    case '/user/delete':
        $controller = new UserController();
        $controller->deleteUser();
        break;

    
    case '/statistic':
        $controller = new StatisticController();
        $controller->homeStatistic();
        break;
    case '/statistic/report':
        $controller = new StatisticController();
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method==='POST'){
            $controller->reportProduct();
        }else{
            $controller->formReportProduct();
        }
        break;
    case '/statistic/resume':
        $controller = new StatisticController();
        break;
    case '/statistic/resume/recap':
        $controller = new StatisticController();
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method==='GET'){            
            $controller->recapProduct($_GET['key']);
        }
        break;

    default:
        http_response_code(404);
        echo "Page non trouvée";
        echo ($uri);
        break;
}

?>

