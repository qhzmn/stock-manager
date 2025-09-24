<?php
namespace Src\Model;

class AlertModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addAlert($id_products, $id_user, $threshold) {
        $stmt = $this->db->prepare("INSERT INTO alerts (id_product, id_user, threshold) VALUES (?, ?, ?)");
        foreach ($id_products as $id_product) {
            $stmt->execute([$id_product, $id_user, $threshold]);
        }
        return $stmt->rowCount() > 0;
    }



    public function deleteAlert($id_product) {
        $stmt = $this->db->prepare("DELETE FROM alerts WHERE id_product = ?");
        $stmt->execute([$id_product]);
        return $stmt->rowCount() > 0;
    }

    public function getAlerts($id_user)
    {
        $stmt = $this->db->prepare("SELECT * FROM alerts JOIN products ON alerts.id_product=products.id_product WHERE id_user = ?");
        $stmt->execute([$id_user]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }   

}


?>
