<?php
namespace Src\Model;


class ProductModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getProducts(array $ids = [], string $search = '', string $sort = 'sku_asc')
    {
        $sql = "SELECT * FROM products";
        $params = [];
        $conditions = [];

        // Filtre par IDs
        if (!empty($ids)) {
            $ids = array_map('intval', $ids);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $conditions[] = "id_product IN ($placeholders)";
            $params = array_merge($params, $ids);
        }

        // Filtre de recherche
        if (!empty($search)) {
            $conditions[] = "(sku LIKE :search 
                            OR name LIKE :search 
                            OR description LIKE :search 
                            OR category LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        // Ajout des conditions
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // Gestion du tri
        switch ($sort) {
            case 'sku_desc':
                $sql .= " ORDER BY sku DESC";
                break;
            case 'name_asc':
                $sql .= " ORDER BY name ASC";
                break;
            case 'name_desc':
                $sql .= " ORDER BY name DESC";
                break;
            case 'purchase_asc':
                $sql .= " ORDER BY purchase_price ASC";
                break;
            case 'purchase_desc':
                $sql .= " ORDER BY purchase_price DESC";
                break;
            case 'selling_asc':
                $sql .= " ORDER BY selling_price ASC";
                break;
            case 'selling_desc':
                $sql .= " ORDER BY selling_price DESC";
                break;
            case 'category_asc':
                $sql .= " ORDER BY category ASC";
                break;
            case 'category_desc':
                $sql .= " ORDER BY category DESC";
                break;
            case 'sku_asc':
            default:
                $sql .= " ORDER BY sku ASC"; // fallback sécurisé
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Use for add quantity in stock
    public function updateQuantity($id_product, $quantity){
        $stmt = $this->db->prepare("UPDATE products SET quantity = ? WHERE id_product = ?");
        $stmt->execute([$quantity, $id_product]);
        return $stmt->rowCount() > 0;
    }
    public function addProduct($sku, $name, $description, $quantity, $purchase, $selling, $category) {
        $stmt = $this->db->prepare("INSERT INTO products (SKU, name, description, quantity, purchase_price, selling_price, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$sku, $name, $description, $quantity, $purchase, $selling, $category]);
        if ($stmt->rowCount() > 0) {
            return $this->db->lastInsertId();
        } else {
            return false; // Insertion échouée
        }
    }
    public function editProduct($id_product, $sku, $name, $description, $quantity, $purchase, $selling, $category) {
        $stmt = $this->db->prepare("UPDATE products SET SKU = ?, name = ?, description = ?, quantity = ?, purchase_price = ?, selling_price = ?, category = ? WHERE id_product = ?");
        $stmt->execute([$sku, $name, $description, $quantity, $purchase, $selling, $category, $id_product]);
        return $stmt->rowCount() > 0;
    }
    public function deleteProduct($id){
        $stmt = $this->db->prepare("DELETE FROM products WHERE id_product = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    
}



    
    

?>
