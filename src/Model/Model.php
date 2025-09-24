<?php
namespace Src\Model;
class ConnectionModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addUser($email, $password, $first_name, $last_name, $type, $group) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (email, password, first_name, last_name, type, group) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$email, $hashedPassword, $first_name, $last_name, $type, $group]);
        return $stmt->rowCount() > 0;
    }

    public function checkEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND is_active IN (0,1) LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? and is_active =1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            return $user; // Connexion réussie
        }
        return false; // Échec d’authentification
    }

    public function history($user, $username, $userAgent, $ip) {
        if (empty($user)){
            $id_user=NULL;
            $succes = 0;

        }else{
            $id_user=$user["id_user"];
            $succes = 1;
        }
        $stmt = $this->db->prepare("INSERT INTO login_history (id_user, email, ip_address, user_agent, success) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id_user, $username, $ip, $userAgent, $succes]);
        
    }

    
    
}
?>
