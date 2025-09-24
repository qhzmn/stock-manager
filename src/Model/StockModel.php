<?php
namespace Src\Model;


class StockModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getMovements($search = '', $sort = 'date_desc') {
        $sql = "SELECT id_movement, movements.id_product, name, movements.sku, first_name, last_name, movements.type, movements.quantity, movements.date, comment FROM movements JOIN products ON movements.id_product=products.id_product JOIN users ON movements.id_user=users.id_user";
        $params = [];
        // Ajout du filtre de recherche
        if (!empty($search)) {
            $sql .= " WHERE SKU LIKE :search
                    OR name LIKE :search 
                    OR first_name LIKE :search
                    OR last_name LIKE :search 
                    OR movement_type LIKE :search
                    OR comment LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        // Gestion du tri
        switch ($sort) {
            case 'sku_asc':
                $sql .= " ORDER BY SKU ASC";
                break;
            case 'sku_desc':
                $sql .= " ORDER BY SKU DESC";
                break;
            case 'name_asc':
                $sql .= " ORDER BY name ASC";
                break;
            case 'name_desc':
                $sql .= " ORDER BY name DESC";
                break;
            case 'firstlastname_asc':
                $sql .= " ORDER BY name ASC";
                break;
            case 'firstlastname_desc':
                $sql .= " ORDER BY name DESC";
                break;
            case 'movement_asc':
                $sql .= " ORDER BY movement_type ASC";
                break;
            case 'movement_desc':
                $sql .= " ORDER BY movement_type DESC";
                break;
            case 'quantity_asc':
                $sql .= " ORDER BY movements.quantity ASC";
                break;
            case 'quantity_desc':
                $sql .= " ORDER BY movements.quantity DESC";
                break;
            case 'date_asc':
                $sql .= " ORDER BY date ASC";
                break;
            case 'date_desc':
                $sql .= " ORDER BY date DESC";
                break;
            default:
                $sql .= " ORDER BY date DESC"; // fallback pour éviter une injection
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
    }


    

    

       
    
}
?>
