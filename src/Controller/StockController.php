<?php

namespace Src\Controller;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
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
        $sort = $_GET['sort'] ?? 'sku_asc';
        if ($action==='reset'){
            $search='';
        }
        $error = $_SESSION['stock_error'] ?? null;
        $succes = $_SESSION['stock_succes'] ?? null;
        unset($_SESSION['stock_error']);   
        unset($_SESSION['stock_succes']);
        $stockModel = new ProductModel($pdo);
        $movements = $stockModel->getProducts([], $search, $sort);
        $menu = ['entry' => '+ Stock entry', 'exit' => '- Stock removal', 'alert' => 'Stock alert', 'movement' => 'Stock movement'];
        $headers = [
            'sku'            => 'SKU',
            'name'           => 'Name',
            'quantity'    => 'Quantity',
            'purchase_price' => 'Purchase price (€)',
            'selling_price'  => 'Selling price (€)'
        ];
        $sortable_columns = $sortable_columns = ['sku_asc' => 'SKU 1→9', 'sku_desc' => 'SKU 9→1', 'name_asc' => 'Name A→Z',
        'name_desc' => 'Name Z→A', 'purchase_asc' => 'Purchase price 1→9', 'purchase_desc'=> 'Purchase price 9→1', 'selling_asc' => 'Selling price 1→9',
        'selling_desc' => 'Selling price 9→1', 'quantity_asc' => 'Quantity 1→9', 'quantity_desc'=> 'Quantity 9→1'];
        echo $this->twig->render('template_home.html.twig',[
            'title' => 'Manage stocks',
            //'button_add' => ['+ Product', 'product/add'],
            'button_menu' => $menu,
            'search' => $search,
            'path' => 'stock', 
            'sort' => $sort,
            'sortable_columns' => $sortable_columns,
            'subtitle' => 'RESULT',
            'headers' => $headers,
            'data' => $movements,
            'id' => 'id_stock',
            //'user_type' => $userType,
            'error' => $error,
            'succes' => $succes
        ]);
        
        
    }
    
    public function movementStock(string $mode){
    require_once __DIR__ . '/../Model/ProductModel.php';
    require_once __DIR__ . '/../../config/database.php';
    $model = new ProductModel($pdo);
    $selected_products = $_SESSION['id_variable'] ?? null;
    $products = [];
    if (!empty($selected_products)) {
        $products = $model->getProducts($selected_products, '', '');
        if (!$products) {
            $_SESSION['product_error'] = "Error get product";
            header('Location: /stock');
            exit; 
        }
    }
    $error = $_SESSION['product_error'] ?? null;
    unset($_SESSION['product_error']);
    // Définition dynamique des titres, actions et labels
    $titles = [
        'exit' => 'Stock removal view',
        'entry' => 'Stock receipts view'
    ];
    $actions = [
        'exit' => '/stock/exit/add',
        'entry' => '/stock/entry/add'
    ];
    $qtyLabels = [
        'exit' => 'Quantity removed',
        'entry' => 'Quantity added'
    ];

    echo $this->twig->render('template_movement.html.twig', [
        'title' => $titles[$mode] ?? 'Stock movement',
        'action' => $actions[$mode] ?? '/stock',
        'products' => $products,
        'qtyLabel' => $qtyLabels[$mode] ?? 'Quantity',
        'mode' => $mode,
        'error' => $error
    ]);
}

    
    public function addMovementStock(string $mode)
    {
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../Model/MovementModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $productModel = new ProductModel($pdo);
        $movementModel = new MovementModel($pdo);
        $products = $_POST['products'] ?? [];
        $comment = trim($_POST['comment'] ?? '');
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
        unset($_SESSION['id_variable']);

        $headers = ['sku' => 'SKU', 'name' => 'Name', 'threshold' => 'Threshold', 'actions' => ['label' => 'Actions', 'buttons' => [['/stock/alert/delete', 'Delete this product ?', 'Delete']]]];
        echo $this->twig->render('template_home.html.twig',[
            'title' => 'Manage alerts',
            'button_add' => ['+ Alerts', '/stock/alert/add'],
            //'button_menu' => ['entry' => '+ Stock entry'],
            //'search' => $search,
            'path' => '', 
            //'sort' => $sort,
            //'sortable_columns' => $sortable_columns,
            'subtitle' => 'RESULT',
            'headers' => $headers,
            'data' => $alerts,
            'id' => 'id_alert',
            //'user_type' => $userType,
            //'error' => $error,
            //'succes' => $succes
        ]);

    }
    public function formAddAlert(){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $model = new ProductModel($pdo);
        $selected_products = $_SESSION['id_variable'] ?? null;
        $products=[];
        if (!empty($selected_products)) {
            $products = $model->getProducts($selected_products, '', '');
            if (!$products) {
                $_SESSION['alart_error'] = "Error get product";
                header('Location: /alart');
                exit; 
            }
        }

        $form_fields = [['name' => 'name', 'label' => 'Alert name :*', 'type' => 'text', 'required' => true],[
        'name' => 'sku', 'label' => "SKU du produit :", 'type' => 'selectproduct', 'required' => true],
        ['name' => 'level', 'label' => 'Level stock :', 'type' => 'number', 'required' => true]];
        
        echo $this->twig->render('template_form.html.twig', [
            'subtitle' => 'Add an alert',
            'action' => '/stock/alert/add',
            'products' => $products,
            'mode' => 'addAlert',


            'path' => '/stock/alert/add', 'fields' => $form_fields, 'submit_label' => 'Add']);
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

        $id_product=$_POST['id_alert'];
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
        if ($action==='reset'){
            $search='';
        }
        $error = $_SESSION['stock_error'] ?? null;
        $succes = $_SESSION['stock_succes'] ?? null;
        unset($_SESSION['stock_error']);   
        unset($_SESSION['stock_succes']);
        $movementModel = new MovementModel($pdo);
        
        $movements = $movementModel->getMovements(null, ['entry', 'exit', 'add', 'edit', 'delete'], null, null, $search, $sort);

        
        $headers = ['sku' => 'SKU', 'name' => 'Name', 'first_name_user' => 'First name', 'last_name_user' => 'Last name',
        'type' => 'Type of movement', 'quantity' => 'Quantity', 'date' => 'Date', 'comment' => 'Comment'];
        $sortable_columns = $sortable_columns = ['sku_asc' => 'SKU 1→9', 'sku_desc' => 'SKU 9→1', 'name_asc' => 'Name A→Z',
            'name_desc' => 'Name Z→A', 'first_name_asc' => 'first_name price A→Z', 'first_name_desc'=> 'first_name price Z→A',
            'last_name_asc' => 'Last name A→Z', 'last_name_desc'=> 'Last name Z→A', 'type_asc' => 'Type of movement A→Z',
            'type_desc' => 'Type of movement Z→A', 'quantity_asc' => 'Quantity 1→9', 'quantity_desc' => 'Quantity  9→1',
            'date_asc' => 'Date 1→9', 'date_desc' => 'Date  9→1',];
        echo $this->twig->render('template_home.html.twig',[
            'title' => 'Manage movement',
            //'button_add' => ['+ Product', 'product/add'],
            //'button_menu' => ['entry' => '+ Stock entry'],
            'search' => $search,
            'path' => '/stock/movement', 
            'sort' => $sort,
            'sortable_columns' => $sortable_columns,
            'subtitle' => 'RESULT',
            'headers' => $headers,
            'data' => $movements,
            'id' => 'id_product',
            //'user_type' => $userType,
            'error' => $error,
            'succes' => $succes
        ]);

    }


}
?>
