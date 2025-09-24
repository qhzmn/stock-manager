<?php

namespace Src\Controller;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Src\Model\StockModel;
use Src\Model\ProductModel;
use Src\Model\AlertModel;
use Src\Model\MovementModel;


class StockController
{
    private $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../View');
        $this->twig = new Environment($loader);
    }

    public function homeStock(){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../../config/database.php';
        
        $action = $_GET['action'] ?? '';
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'date_desc';
        if ($action==='reset'){
            $search='';
        }
        $error = $_SESSION['stock_error'] ?? null;
        $succes = $_SESSION['stock_succes'] ?? null;
        unset($_SESSION['stock_error']);   
        unset($_SESSION['stock_succes']);
        $stockModel = new ProductModel($pdo);
        $movements = $stockModel->getProducts([], $search, $sort);
        echo $this->twig->render('home_stock.html.twig', ['movements' => $movements, 'search' => $search, 'sort' => $sort, 'succes' => $succes, 'error' => $error]);
    }
    
    public function movementStock(string $mode){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../../config/database.php';
        if (!empty($_POST['selected_products'])) {
            $model = new ProductModel($pdo);
            $products = $model->getProducts($_POST['selected_products'], '', '');   
            if (!$products) {
                $_SESSION['product_error'] = "Error get product";
                header('Location: /stock');
                exit; 
            }
        }else{
            $products= [];
        }
        $error = $_SESSION['product_error'] ?? null;
        unset($_SESSION['product_error']);
        echo $this->twig->render('movement_stock.html.twig', ['products' => $products, 'mode' => $mode, 'error' => $error]);
    }
    
    public function addMovementStock(string $mode)
    {
        require_once __DIR__ . '/../Model/StockModel.php';
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../Model/MovementModel.php';
        require_once __DIR__ . '/../../config/database.php';

        $products = $_POST['products'] ?? [];
        $comment = trim($_POST['comment'] ?? '');

        // Vérification commentaire
        if (strlen($comment) > 255) {
            $_SESSION['product_error'] = "Comment too long (max 255 chars)";
            header('Location: /stock');
            exit;
        }

        if (empty($products)) {
            $_SESSION['product_error'] = "No products selected.";
            header('Location: /stock');
            exit;
        }

        $productModel = new ProductModel($pdo);
        $movementModel = new MovementModel($pdo);

        foreach ($products as $id_product => $data) {
            $dbProduct = $productModel->getProducts([$id_product], '', '');
            if (!$dbProduct) {
                $_SESSION['product_error'] = "Product not found.";
                header('Location: /stock');
                exit;
            }

            $dbProduct = $dbProduct[0];
            $sku = (string)($data['sku'] ?? '');
            $purchase_price = (float)($dbProduct['purchase_price']);
            $selling_price = (float)($dbProduct['selling_price']);
            $currentStock = (int)($dbProduct['quantity']);
            $inputQuantity = (int)($data['quantity'] ?? 0);

            // Vérifications de base
            if ($inputQuantity < 0 || ($mode === 'exit' && $inputQuantity > $currentStock)) {
                $_SESSION['product_error'] = "Invalid quantity for product $sku.";
                header('Location: /stock');
                exit;
            }

            // Calcul du nouveau stock
            if ($mode === 'exit') {
                $newQuantity = $currentStock - $inputQuantity;
            } elseif ($mode === 'entry') {
                $newQuantity = $currentStock + $inputQuantity;
            } else {
                $_SESSION['product_error'] = "Invalid stock movement mode.";
                header('Location: /stock');
                exit;
            }

            // Mise à jour produit
            $success = $productModel->updateQuantity($id_product, $newQuantity);

            if ($success) {
                // Mouvement principal
                $movementModel->addMovement(
                    $id_product,
                    $sku,
                    $_SESSION['id_user'],
                    $inputQuantity,
                    $purchase_price,
                    $selling_price,
                    $mode,
                    $comment
                );

                // Mouvement "level" (état du stock après opé)
                $movementModel->addMovement(
                    $id_product,
                    $sku,
                    $_SESSION['id_user'],
                    $newQuantity,
                    $purchase_price,
                    $selling_price,
                    'level',
                    ''
                );
            }
        }

        // Redirection après traitement de tous les produits
        header('Location: /stock');
        exit;
    }







    public function homeAlert(){
        require_once __DIR__ . '/../Model/AlertModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $alertModel = new AlertModel($pdo);
        $id_user=$_SESSION['id_user'];
        $alerts = $alertModel->getAlerts($id_user);
        echo $this->twig->render('home_alerts.html.twig', ['alerts' => $alerts]);

    }
    public function formAddAlert(){
        echo $this->twig->render('add_alert.html.twig');
    }
    public function addAlert(){
        require_once __DIR__ . '/../Model/AlertModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $alertModel = new AlertModel($pdo);
        unset($_SESSION['product_error']);
        $id_user=$_SESSION['id_user'];
        $threshold=$_POST['level'];
        $id_products=$_POST['id_products'];
        $alerts = $alertModel->addAlert($id_products, $id_user, $threshold);
        header('Location: /stock/alert');
        exit; 
    }
    public function deleteAlert(){
        require_once __DIR__ . '/../Model/AlertModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $alertModel = new AlertModel($pdo);
        $id_product=$_POST['id_product'];
        $alerts = $alertModel->deleteAlert($id_product);
        header('Location: /stock/alert');
        exit; 
    }








    public function homeMovement(){
        require_once __DIR__ . '/../Model/AlertModel.php';
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $action = $_GET['action'] ?? '';
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'date_desc';
        var_dump($action, $search, $sort);
        if ($action==='reset'){
            $search='';
        }
        $error = $_SESSION['stock_error'] ?? null;
        $succes = $_SESSION['stock_succes'] ?? null;
        unset($_SESSION['stock_error']);   
        unset($_SESSION['stock_succes']);
        $movementModel = new MovementModel($pdo);
        
        $movements = $movementModel->getMovements(null, [], null, null, $search = '', $sort = 'date_desc');

        
        /*if (!empty($_POST['quantities'])) {
            foreach ($_POST['quantities'] as $id_product => $quantity) {
                $productModel = new ProductModel($pdo);
                $state=$productModel->addQuantity($id_product, $quantity);
                if ($state) {
                    $state=$stockModel->addMovement($id_product, $_SESSION['id_user'], 'entry', $quantity, 'Stock entry');
                    $_SESSION['stock_succes'] = "Stock updated";
                } else {
                    $_SESSION['stock_error'] = "Error updating stock";
                }
                
            }
        }
        
        $stockModel = new StockModel($pdo);
        $movements = $stockModel->getMovements($search, $sort);*/

        echo $this->twig->render('home_movement.html.twig', ['movements' => $movements, 'search' => $search, 'sort' => $sort, 'succes' => $succes, 'error' => $error]);
    }


}
?>
