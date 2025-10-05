<?php

namespace Src\Controller;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Src\Model\MovementModel;
use Src\Model\ProductModel;





class StatisticController
{
    private $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../View');
        $this->twig = new Environment($loader);
    }




    public function homeStatistic(){
        require_once __DIR__ . '/../Model/MovementModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $action = $_GET['action'] ?? '';
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'date_desc';
        if ($action==='reset'){
            $search='';
        }
        $error = $_SESSION['statistic_error'] ?? null;
        $succes = $_SESSION['statistic_succes'] ?? null;
        unset($_SESSION['statistic_error']);   
        unset($_SESSION['statistic_succes']);
        $movementModel = new MovementModel($pdo);
        
        $movements = $movementModel->getMovements(null, [], null, null, $search, $sort);


        $headers = ['sku' => 'SKU', 'name' => 'Name', 'first_name_user' => 'First name', 'last_name_user' => 'Last name',
        'type' => 'Type of movement', 'quantity' => 'Quantity', 'date' => 'Date', 'comment' => 'Comment'];
        $sortable_columns = $sortable_columns = ['sku_asc' => 'SKU 1→9', 'sku_desc' => 'SKU 9→1', 'name_asc' => 'Name A→Z',
            'name_desc' => 'Name Z→A', 'first_name_asc' => 'first_name price A→Z', 'first_name_desc'=> 'first_name price Z→A',
            'last_name_asc' => 'Last name A→Z', 'last_name_desc'=> 'Last name Z→A', 'type_asc' => 'Type of movement A→Z',
            'type_desc' => 'Type of movement Z→A', 'quantity_asc' => 'Quantity 1→9', 'quantity_desc' => 'Quantity  9→1',
            'date_asc' => 'Date 1→9', 'date_desc' => 'Date  9→1',];
        echo $this->twig->render('template_home.html.twig',[
            'title' => 'Statistics',
            //'button_add' => ['+ Product', 'product/add'],
            'button_menu' => ['report' => 'Generate a report', 'resume' => 'Product statistics'],
            'search' => $search,
            'path' => 'statistic', 
            'sort' => $sort,
            'sortable_columns' => $sortable_columns,
            'subtitle' => 'RESULT',
            'headers' => $headers,
            'data' => $movements,
            'id' => 'id_movement',
            //'user_type' => $userType,
            'error' => $error,
            'succes' => $succes
        ]);
    }

    public function formReportProduct(){
        require_once __DIR__ . '/../Model/ProductModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $productModel = new ProductModel($pdo);
        $selected_products = $_SESSION['id_variable'] ?? null;
        $products=[];
        $error = $_SESSION['statistic_error'] ?? null;
        unset($_SESSION['statistic_error']);
        if (!empty($selected_products)) {
            $products = $productModel->getProducts($selected_products, '', '');
            if (!$products) {
                $_SESSION['alart_error'] = "Error get product";
                header('Location: /alart');
                exit; 
            }
        }
        $form_fields = [['name' => 'date1', 'label' => 'Start date :', 'type' => 'date', 'required' => true],
        ['name' => 'date2', 'label' => 'End date :', 'type' => 'date', 'required' => true],
        ['name' => 'sku', 'label' => "SKU du produit :", 'type' => 'selectproduct', 'required' => true]];  
        echo $this->twig->render('template_form.html.twig', [
            'subtitle' => 'Create a report',
            'action' => '/statistic/report',
            'products' => $products,
            'mode' => 'addReport',
            'error' => $error,
            'path' => '/statistic/report', 'fields' => $form_fields, 'submit_label' => 'Create']);
    }

    public function reportProduct(){
        require_once __DIR__ . '/../Model/MovementModel.php';
        require_once __DIR__ . '/../../config/database.php';
        $date1 = trim($_POST['date1'] ?? null);
        $date2 = trim($_POST['date2'] ?? null);
        $id_products = $_POST['id_products'] ?? [];
        if (empty($date1) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date1)){
            $_SESSION['statistic_error'] = "Error date 1";
        }elseif (empty($date2) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date2)){
            $_SESSION['statistic_error'] = "Error date 2";
        }elseif (strtotime($date2) < strtotime($date1)){
            $_SESSION['statistic_error'] = "Error date";
        }elseif (empty($id_products) || !is_array($id_products)) {
            $_SESSION['statistic_error'] = "Error id";
        }
        if (isset($_SESSION['statistic_error']) && !empty($_SESSION['statistic_error'])) {
            header("Location: /statistic/report");
            exit;
        }
        $movementModel = new MovementModel($pdo);
        $movements = $movementModel->getMovements($id_products, [], $date1, $date2, '', '');
        
        if (empty($movements)){
            $_SESSION['statistic_error'] = "Error";
            header("Location: /statistic/report");
            exit;
        }
        $filename = "export_" . date("Y-m-d_H-i-s") . ".csv";
        // Headers pour forcer le téléchargement
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $fp = fopen("php://output", "w");
        fputcsv($fp, array_keys($movements[0]), ";", '"', "\\");
        // Parcourir les données et écrire dans le CSV
        foreach ($movements as $row) {
            fputcsv($fp, $row, ";", '"', "\\");
        }
        fclose($fp);
        exit;

    }
    public function recapProduct($key){
    require_once __DIR__ . '/../Model/MovementModel.php';
    require_once __DIR__ . '/../Model/ProductModel.php';
    require_once __DIR__ . '/../../config/database.php';

    $movementModel = new MovementModel($pdo);
    $productModel = new ProductModel($pdo);

    // Produit le plus vendu
    $more_selling = $movementModel->getRecapProduct('DESC');
    $top_selling = $more_selling[0] ?? ['id_product' => '-', 'level' => 0];

    // Produit le moins vendu
    $less_selling = $movementModel->getRecapProduct('ASC');
    $least_selling = $less_selling[0] ?? ['id_product' => '-', 'level' => 0];

    // Stock faible
    $low_stock_data = $productModel->getRecapProduct(1);
    $low_stock = $low_stock_data[0] ?? ['name' => '-', 'level' => 0];

    // Rupture de stock
    $out_stock_data = $productModel->getRecapProduct(0);
    $out_stock = $out_stock_data[0] ?? ['name' => '-', 'level' => 0];

    // Retourne les données sous forme de JSON pour le frontend
    header('Content-Type: application/json');
    echo json_encode([
        'title' => 'Statistiques produit',
        'top_selling' => $top_selling,
        'least_selling' => $least_selling,
        'low_stock' => $low_stock,
        'out_of_stock' => $out_stock
    ]);
}









    

    
    


}
?>
