<?php

namespace Src\Controller;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Src\Model\ProductModel;
use Src\Model\MovementModel;



class ProductController
{
    private $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../View');
        $this->twig = new Environment($loader);
    }

    public function homeProduct(){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $productModel = new ProductModel($pdo);
        $userType = $_SESSION['type'];
        $action = $_GET['action'] ?? '';
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'sku_asc';
        if ($action==='reset'){
            $search='';
        }   
        $products = $productModel->getProducts([], $search, $sort);
        $error = $_SESSION['product_error'] ?? null;
        $succes = $_SESSION['product_succes'] ?? null;
        unset($_SESSION['product_error']);         
        unset($_SESSION['product_succes']);  
        echo $this->twig->render('home_products.html.twig', ['user_type' => $userType, 'products' => $products, 'search' => $search, 'sort' => $sort, 'error' => $error, 'succes' => $succes]);
    }


    public function formSelectProduct(){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $_SESSION['return'] = $_GET['callback'];
        $_SESSION['return'] = $_GET['callback'];
        $productModel = new ProductModel($pdo);
        $products = $productModel->getProducts([], '', '');
        $selected_ids=$_SESSION['id_products'] ?? [];
        unset($_SESSION['id_products']);        
        echo $this->twig->render('select_product.html.twig', ['products' => $products, 'selected_ids' => $selected_ids]);
    }
    public function selectProduct(){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $return = $_SESSION['return'];
        unset($_SESSION['return']);
        if (!empty($_POST['selected_products'])) {
            $model = new ProductModel($pdo);
            $products = $model->getProducts($_POST['selected_products'], '', '');
            $id_products = array_column($products, 'id_product');
            $_SESSION['id_products'] = $id_products;
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
        switch ($return) {
            case 'addAlert':
                echo $this->twig->render('add_alert.html.twig', ['products' => $products]);
                break;
            case 'entrystock':
                echo $this->twig->render('movement_stock.html.twig', ['products' => $products, 'mode' => 'entry']);
                break;
            case 'exitstock':
                echo $this->twig->render('movement_stock.html.twig', ['products' => $products, 'mode' => 'exit']);
                break;
            default:
                break;}  
    }



    public function formAddProduct(){
        $userType = $_SESSION['type'];
        $error = $_SESSION['product_error'] ?? null;
        unset($_SESSION['product_error']);
        echo $this->twig->render('add_product.html.twig', ['user_type' => $userType, 'error' => $error]);
    }
    public function addProduct(){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../Model/MovementModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $productModel = new ProductModel($pdo);
        $sku = trim($_POST['SKU']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description'] ?? null);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $purchase = (float)($_POST['purchase'] ?? 0);
        $selling = (float)($_POST['selling'] ?? 0);
        $category = trim($_POST['category'] ?? null);
        if (empty($sku) || empty($name)) {
            $_SESSION['product_error'] = "Champs manquants.";
            header('Location: /product/add'); 
            exit;
        }
        $id_product = $productModel->addProduct($sku, $name, $description, $quantity, $purchase, $selling, $category);
        if ($id_product) {
            $productModel = new MovementModel($pdo);
            $productModel->addMovement($id_product, $sku, $_SESSION['id_user'], $quantity, $purchase, $selling, 'add', '');
            $_SESSION['product_succes'] = "Product addition succes";
        } else {
            $_SESSION['product_error'] = "Product addition error";
        }
        header('Location: /product');
        exit;    
    }


    public function formEditProduct() {
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $productModel = new ProductModel($pdo);
        $product = $productModel->getProducts([$_POST['id_product']], '', '');
        if (!$product) {
            $_SESSION['product_error'] = "Erreur modifier produit";
            header('Location: /product');
            exit; 
        }
        $error = $_SESSION['product_error'] ?? null;
        unset($_SESSION['product_error']);
        echo $this->twig->render('edit_product.html.twig', ['product' => $product[0], 'error' => $error]);
    }
    public function editProduct(){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../Model/MovementModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $productModel = new ProductModel($pdo);
        $id_product = $_POST['id_product'] ?? '';
        $sku = trim($_POST['SKU']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $purchase = (float)($_POST['purchase'] ?? 0);
        $selling = (float)($_POST['selling'] ?? 0);
        $category = trim($_POST['category'] ?? null);  
        if (empty($id_product) || empty($sku) || empty($name)) {
            $_SESSION['product_error'] = "Champs manquants.";  
            header('Location: /product/edit'); 
            exit;
        }
        $success = $productModel->editProduct($id_product, $sku, $name, $description, $quantity, $purchase, $selling, $category);
        if ($success) {
            $productModel = new MovementModel($pdo);
            $productModel->addMovement($id_product, $sku, $_SESSION['id_user'], $quantity, $purchase, $selling, 'edit', '');
            $_SESSION['product_succes'] = "Product edition succes";
        } else {
            $_SESSION['product_error'] = "Product edition error";
        }
        header('Location: /product');
        exit;    
    }

    public function deleteProduct(){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../Model/MovementModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $id_product = $_POST['id_product'] ?? null;
        $productModel = new ProductModel($pdo);
        $product = $productModel->getProducts([$id_product], '', '');
        $success  = $productModel->deleteProduct($id_product);
        if ($success) {
            $productModel = new MovementModel($pdo);
            $productModel->addMovement($id_product, $product[0]['sku'], $_SESSION['id_user'], null, null, null, 'delete', '');
            $_SESSION['product_succes'] = "Succes produit supprimer";
        } else {
            $_SESSION['product_error'] = "Erreur supprimer produit";
        }
        header('Location: /product');
        exit;
    }

    public function statProduct($id){
        require_once __DIR__ . '/../Model/MovementModel.php';
        require_once __DIR__ . '/../../config/database.php';
        
        $productModel = new MovementModel($pdo);
        $stats  = $productModel->getStatProduct($id);
        header('Content-Type: application/json; charset=utf-8');
        if ($stats) {
            $labels = [];
            $purchasePrices = [];
            $sellingPrices = [];
            $quantities = [];

            foreach ($stats as $row) {
                $labels[] = date('Y-m-d', strtotime($row['date']));
                $purchasePrices[] = (float)$row['purchase_price'];
                $sellingPrices[] = (float)$row['selling_price'];
                $quantities[] = (int)$row['quantity'];
            }
            
            
            echo json_encode([
                "labels" => $labels,
                "datasets" => [
                    ["label" => "Purchase Price", "data" => $purchasePrices],
                    ["label" => "Selling Price", "data" => $sellingPrices],
                    ["label" => "Quantity", "data" => $quantities],
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            exit;
        } else {
            echo json_encode([
                "labels" => [],
                "datasets" => []
            ]);
            exit;
        }


    
        

    }

    


    


}
?>
