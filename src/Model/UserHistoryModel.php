<?php
namespace Src\Model;

function getUserIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        // IP depuis un client partagé
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // IP derrière un proxy ou load balancer
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]); // première IP de la liste
    } else {
        // IP directe
        return $_SERVER['REMOTE_ADDR'];
    }
}

class UserHistoryModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addUserHistory($id_user, $email, $type, $succes) {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu';
        $ip_address = getUserIp();
        $stmt = $this->db->prepare("INSERT INTO users_history (id_user, email, ip_address, user_agent, type, success) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_user, $email, $ip_address, $userAgent, $type, $succes]); 
    }

    
    
}
?>
