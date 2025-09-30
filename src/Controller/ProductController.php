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
        $headers = ['sku' => 'SKU', 'name' => 'Name', 'description' => 'Description', 'purchase_price' => 'Purchase price (€)',
            'selling_price' => 'Selling price (€)', 'category' => 'Category', 'actions' => ['label' => 'Actions', 'buttons' => [['/product/edit', 'Edit this product ?', 'Edit'], ['/product/delete', 'Delete this product ?', 'Delete']]]];
        $sortable_columns = $sortable_columns = ['sku_asc' => 'SKU 1→9', 'sku_desc' => 'SKU 9→1', 'name_asc' => 'Name A→Z',
            'name_desc' => 'Name Z→A', 'purchase_asc' => 'Purchase price 1→9', 'purchase_desc'=> 'Purchase price 9→1', 'selling_asc' => 'Selling price 1→9',
            'selling_desc' => 'Selling price 9→1', 'category_asc' => 'Category A→Z', 'category_desc'=> 'Category Z→A'];
        echo $this->twig->render('template_home.html.twig',[
            'title' => 'Manage products',
            'button_add' => ['+ Product', 'product/add'],
            //'button_menu' => ['entry' => '+ Stock entry'],
            'search' => $search,
            'path' => 'product', 
            'sort' => $sort,
            'sortable_columns' => $sortable_columns,
            'subtitle' => 'RESULT',
            'headers' => $headers,
            'data' => $products,
            'id' => 'id_product',
            'user_type' => $userType,
            'error' => $error,
            'succes' => $succes
        ]);
    }

    public function formAddProduct(){
        $userType = $_SESSION['type'];
        $error = $_SESSION['product_error'] ?? null;
        unset($_SESSION['product_error']);
        $form_fields = [['name' => 'sku', 'label' => 'Product SKU :', 'type' => 'text', 'required' => true],
        ['name' => 'name', 'label' => 'Product name :', 'type' => 'text', 'required' => true],
        ['name' => 'description', 'label' => 'Description :', 'type' => 'textarea', 'required' => false ],
        ['name' => 'quantity', 'label' => 'Quantity :', 'type' => 'number', 'required' => false],
        ['name' => 'purchase', 'label' => 'Purchase price (€) :', 'type' => 'number', 'step' => '0.01', 'required' => false],
        ['name' => 'selling', 'label' => 'Selling price (€) :', 'type' => 'number', 'step' => '0.01', 'required' => false],
        ['name' => 'category', 'label' => 'Category :', 'type' => 'text', 'required' => false]];
        echo $this->twig->render('template_form.html.twig', [
            'subtitle' => 'Add a product',
            'action' => '/product/add',
            'fields' => $form_fields,
            'submit_label' => 'Add',
            'user_type' => $userType,
            'error' => $error
        ]);
    }
    public function addProduct(){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../Model/MovementModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $productModel = new ProductModel($pdo);
        $sku = trim($_POST['sku']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description'] ?? null);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $purchase = (float)($_POST['purchase'] ?? 0);
        $selling = (float)($_POST['selling'] ?? 0);
        $category = trim($_POST['category'] ?? null);
        if (empty($sku) || !preg_match("/^[A-Za-z0-9\s-]{3,20}$/", $sku)) {
            $_SESSION['product_error'] = "SKU must be 3-20 characters and contain only letters/numbers.";
        }elseif (empty($name) || !preg_match("/^[A-Za-z0-9\s-]{3,50}$/", $name)) {
            $_SESSION['product_error'] = "Name must be 3-50 characters and contain only letters/numbers.";
        }elseif ($description && !preg_match("/^[A-Za-z0-9\s-]{0,255}$/", $description)) {
            $_SESSION['product_error'] = "Description can contain only letters/numbers, max 255 chars.";
        }elseif ($category && !preg_match("/^[A-Za-z0-9\s-]{0,50}$/", $category)) {
            $_SESSION['product_error'] = "Category can contain only letters/numbers, max 50 chars.";
        }elseif ($quantity !== '' && (!is_numeric($quantity) || $quantity < 0)) {
            $_SESSION['product_error'] = "Quantity must be a positive number.";
        }elseif ($purchase !== '' && (!is_numeric($purchase) || $purchase < 0)) {
            $_SESSION['product_error'] = "Purchase price must be a positive number.";
        }elseif ($selling !== '' && (!is_numeric($selling) || $selling < 0)) {
            $_SESSION['product_error'] = "Selling price must be a positive number.";
        }elseif (!($productModel->checkSku($sku))){
            $_SESSION['product_error'] = "This sku is already registered.";
        }    
        if (isset($_SESSION['product_error']) && !empty($_SESSION['product_error'])){
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
        $form_fields = [['name' => 'id_product', 'type' => 'hidden', 'value' => $product[0]['id_product'] ?? ''],
        ['name' => 'sku', 'label' => 'Product SKU :*', 'type' => 'text', 'required' => true, 'value' => $product[0]['sku'] ?? ''],
        ['name' => 'name', 'label' => 'Product name :*', 'type' => 'text', 'required' => true, 'value' => $product[0]['name'] ?? ''],
        ['name' => 'description', 'label' => 'Description :*', 'type' => 'textarea', 'required' => false, 'value' => $product[0]['description'] ?? ''],
        ['name' => 'quantity', 'label' => 'Quantity :', 'type' => 'number', 'required' => false, 'value' => $product[0]['quantity'] ?? ''],
        ['name' => 'purchase', 'label' => 'Purchase price (€) :', 'type' => 'number', 'step' => '0.01', 'required' => false, 'value' => $product[0]['purchase_price'] ?? ''],
        ['name' => 'selling', 'label' => 'Selling price (€) :', 'type' => 'number', 'step' => '0.01', 'required' => false, 'value' => $product[0]['selling_price'] ?? ''],
        ['name' => 'category', 'label' => 'Category :', 'type' => 'text', 'required' => false, 'value' => $product[0]['category'] ?? '']];
        echo $this->twig->render('template_form.html.twig', [
            'subtitle' => 'Edit this product',
            'action' => '/product/edit',
            'fields' => $form_fields,
            'submit_label' => 'Edit',
            //'user_type' => $userType,
            'error' => $error
        ]);
    }
    public function editProduct(){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../Model/MovementModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $productModel = new ProductModel($pdo);

        $id_product = $_POST['id_product'] ?? '';
        $sku = trim($_POST['sku']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description'] ?? null);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $purchase = (float)($_POST['purchase'] ?? 0);
        $selling = (float)($_POST['selling'] ?? 0);
        $category = trim($_POST['category'] ?? null);  
        if (empty($sku) || !preg_match("/^[A-Za-z0-9\s-]{3,20}$/", $sku)) {
            $_SESSION['product_error'] = "SKU must be 3-20 characters and contain only letters/numbers.";
        }elseif (empty($name) || !preg_match("/^[A-Za-z0-9\s-]{3,50}$/", $name)) {
            $_SESSION['product_error'] = "Name must be 3-50 characters and contain only letters/numbers.";
        }elseif ($description && !preg_match("/^[A-Za-z0-9\s-]{0,255}$/", $description)) {
            $_SESSION['product_error'] = "Description can contain only letters/numbers, max 255 chars.";
        }elseif ($category && !preg_match("/^[A-Za-z0-9\s-]{0,50}$/", $category)) {
            $_SESSION['product_error'] = "Category can contain only letters/numbers, max 50 chars.";
        }elseif ($quantity !== '' && (!is_numeric($quantity) || $quantity < 0)) {
            $_SESSION['product_error'] = "Quantity must be a positive number.";
        }elseif ($purchase !== '' && (!is_numeric($purchase) || $purchase < 0)) {
            $_SESSION['product_error'] = "Purchase price must be a positive number.";
        }elseif ($selling !== '' && (!is_numeric($selling) || $selling < 0)) {
            $_SESSION['product_error'] = "Selling price must be a positive number.";
        }elseif (!($productModel->checkSku($sku))){
            $_SESSION['product_error'] = "This sku is already registered.";
        }
        if (isset($_SESSION['product_error']) && !empty($_SESSION['product_error'])){
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
            header('Location: /product/edit');
            exit;   
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








    

public function formSelectProduct(){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $selected_ids = $_SESSION['id_variable'] ?? null;
        $return = $_GET['callback'];
        $productModel = new ProductModel($pdo);
        $products = $productModel->getProducts([], '', '');
        unset($_SESSION['id_products']);        
        echo $this->twig->render('select_product.html.twig', ['return' => $return, 'products' => $products, 'selected_ids' => $selected_ids]);
    }
    public function selectProduct(){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $return = $_POST['return'] ?? null;
        $selected_products = $_POST['selected_products'] ?? null;
        $id_products= [];
        if (empty($return)){
            $_SESSION['product_error'] = "Error get return";
            header('Location: /stock');
            exit; 
        }elseif (!empty($_POST['selected_products'])){
            $model = new ProductModel($pdo);
            $products = $model->getProducts($_POST['selected_products'], '', '');
            if (!$products) {
                $_SESSION['product_error'] = "Error get product";
                header('Location: /stock');
                exit; 
            }
            $id_products = array_column($products, 'id_product');
        }
        $_SESSION['id_variable']=$id_products;
        switch ($return) {
            case 'addAlert':
                header("Location: /stock/alert/add");
                exit;
            case 'entrystock':
                header("Location: /stock/entry");
                break;
            case 'exitstock':
                header("Location: /stock/exit");
            case 'addReport':
                header("Location: /statistic/report");
            default:
                break;}  
    }

    


}
?>
