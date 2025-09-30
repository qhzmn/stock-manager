<?php
namespace Src\Model;

class MovementModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addMovement($id_product, $sku, $id_user, $quantity, $purchase_price, $selling_price, $type, $comment) {
        $stmt = $this->db->prepare("INSERT INTO movements (id_product, sku, quantity, id_user, purchase_price, selling_price, type, comment) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_product, $sku, $quantity, $id_user, $purchase_price, $selling_price, $type, $comment]);
        return $stmt->rowCount() > 0;
    }

    public function getMovements($id_product = null, array $types = [], $start_date = null, $end_date = null, $search = '', $sort = 'date_desc')
{
    $sql = "SELECT 
                m.sku, 
                m.comment, 
                m.date, 
                m.purchase_price, 
                m.selling_price, 
                m.quantity, 
                m.type, 
                u.last_name, 
                u.first_name
            FROM movements m
            JOIN users u ON m.id_user = u.id_user
            WHERE 1=1";

    $params = [];

    // Filtre par types
    if (!empty($types)) {
        $placeholders = [];
        foreach ($types as $index => $val) {
            $placeholders[] = ":type{$index}";
            $params[":type{$index}"] = $val; // garder la valeur string si besoin
        }
        $sql .= " AND m.type IN (" . implode(',', $placeholders) . ")";
    }

    // Filtre par produit
    if (!empty($id_products)) {
        $placeholders = [];
        foreach ($id_products as $index => $val) {
            $placeholders[] = ":id_product{$index}";
            $params[":id_product{$index}"] = $val;
        }
        $sql .= " AND m.id_product IN (" . implode(',', $placeholders) . ")";
    }


    // Filtre par date
    if ($start_date && $end_date) {
        $sql .= " AND m.date BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
    }

    // Recherche texte
    if (!empty($search)) {
        $sql .= " AND (m.sku LIKE :search OR m.type LIKE :search OR m.comment LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    // Tri sécurisé
    $allowedSort = [
        'sku_asc' => 'm.sku ASC',
        'sku_desc' => 'm.sku DESC',
        'purchase_asc' => 'm.purchase_price ASC',
        'purchase_desc' => 'm.purchase_price DESC',
        'selling_asc' => 'm.selling_price ASC',
        'selling_desc' => 'm.selling_price DESC',
        'type_asc' => 'm.type ASC',
        'type_desc' => 'm.type DESC',
        'date_asc' => 'm.date ASC',
        'date_desc' => 'm.date DESC',
    ];

    $orderBy = $allowedSort[$sort] ?? 'm.date DESC';
    $sql .= " ORDER BY $orderBy";
    

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

    




 
    

}



    
    

?>
