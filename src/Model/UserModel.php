<?php
namespace Src\Model;


class UserModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? and is_active =1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false; 
    }

    

    public function addUser($email, $password, $typecompte, $group){
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (email, password, type, groupe) VALUES (?, ?, ?, ?)");
        $stmt->execute([$email, $hashedPassword, $typecompte, $group]);
        return $stmt->rowCount() > 0;
    }


    public function getGroup($id_user) {
    $stmt = $this->db->prepare("SELECT * FROM users WHERE groupe = ?");
    $stmt->execute([$id_user]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }



    

    
    
}
?>
