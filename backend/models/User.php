<?php
require_once __DIR__ . '/Model.php';

class User extends Model {
    protected $table_name = "users";

    public function login($email, $password) {
        $query = "SELECT id, username, email, password, api_token FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                // Generate a new simple token if needed or return existing
                // For this example, we return existing or generate a random string
                $token = bin2hex(random_bytes(16));
                $this->updateToken($row['id'], $token);
                
                unset($row['password']);
                $row['token'] = $token;
                return $row;
            }
        }
        return false;
    }

    public function updateToken($id, $token) {
        $query = "UPDATE " . $this->table_name . " SET api_token = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $token);
        $stmt->bindParam(2, $id);
        return $stmt->execute();
    }
    
    public function validateApiToken($token) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE api_token = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $token);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    // Create admin user for testing
    public function create($username, $email, $password) {
        $query = "INSERT INTO " . $this->table_name . " (username, email, password) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, $email);
        $stmt->bindParam(3, $password_hash);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
