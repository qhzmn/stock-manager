<?php
namespace Src\Model;
class UserHistoryModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }



    public function adduser($username, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        
        $stmt->execute([$username, $hashedPassword]);
        return 1;
        
    }

    
    

    

    public function userHistory($id_user, $email, $ip_address, $userAgent, $type, $succes) {
        $stmt = $this->db->prepare("INSERT INTO users_history (id_user, email, ip_address, user_agent, type, success) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_user, $email, $ip_address, $userAgent, $type, $succes]); 
    }

    
    
}
?>
