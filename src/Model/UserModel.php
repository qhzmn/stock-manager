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
    public function checkEmail($email) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $count = $stmt->fetchColumn(); // récupère directement la valeur du COUNT
        return $count == 0;
    }



    public function addUser($email, $password, $first_name, $last_name, $type, $group){
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (email, password, first_name, last_name, type, groupe) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$email, $hashedPassword, $first_name, $last_name, $type, $group]);
        if ($stmt->rowCount() > 0){
            return $this->db->lastInsertId();
        }
        return false;
    }
    public function deleteUser($id_user){
        $stmt = $this->db->prepare("UPDATE users SET password = '', is_active = 2 WHERE id_user = ?");
        $stmt->execute([$id_user]);
        return $stmt->rowCount() > 0;
    }
    public function editUser($id_user, $email, $password, $first_name, $last_name, $type) {
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users 
                SET email = ?, password = ?, first_name = ?, last_name = ?, type = ?
                WHERE id_user = ?";
        $params = [$email, $hashedPassword, $first_name, $last_name, $type, $id_user];
    } else {
        $sql = "UPDATE users 
                SET email = ?, first_name = ?, last_name = ?, type = ?
                WHERE id_user = ?";
        $params = [$email, $first_name, $last_name, $type, $id_user];
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->rowCount() > 0;
}

    public function getUser($id_user){
        $stmt = $this->db->prepare("SELECT email, first_name, last_name, type, groupe FROM users WHERE id_user = ?");
        $stmt->execute([$id_user]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);

    }


        
    public function getGroup(int $id_user, string $search = '', string $sort = 'email_asc') {
        $sql = "SELECT id_user, email, first_name, last_name, type, groupe 
                FROM users 
                WHERE groupe = :id_user 
                AND is_active = 1";

        $params = [':id_user' => $id_user];

        // Ajout de recherche
        if (!empty($search)) {
            $sql .= " AND (
                email LIKE :search
                OR last_name LIKE :search
                OR first_name LIKE :search
                OR type LIKE :search
            )";
            $params[':search'] = '%' . $search . '%';
        }

        // Tri
        switch ($sort) {
            case 'email_desc':
                $sql .= " ORDER BY email DESC";
                break;
            case 'last_name_asc':
                $sql .= " ORDER BY last_name ASC";
                break;
            case 'last_name_desc':
                $sql .= " ORDER BY last_name DESC";
                break;
            case 'first_name_asc':
                $sql .= " ORDER BY first_name ASC";
                break;
            case 'first_name_desc':
                $sql .= " ORDER BY first_name DESC";
                break;
            case 'type_asc':
                $sql .= " ORDER BY type ASC";
                break;
            case 'type_desc':
                $sql .= " ORDER BY type DESC";
                break;
            case 'email_asc':
            default:
                $sql .= " ORDER BY email ASC"; // fallback sécurisé
                break;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }




    

    
    
}
?>
