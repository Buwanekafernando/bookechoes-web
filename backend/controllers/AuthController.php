<?php
require_once __DIR__ . '/../config/Database.php';

class AuthController {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->email) || !isset($data->password)) {
            http_response_code(400);
            echo json_encode(array("message" => "Incomplete login data."));
            return;
        }

        $query = "SELECT id, username, email, password FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $data->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $password = $data->password;
            $hashed_password = $row['password'];

            if (password_verify($password, $hashed_password)) {
                // Generate Token
                $token = bin2hex(random_bytes(32));
                
                // Update Token in DB
                $update_query = "UPDATE " . $this->table_name . " SET api_token = ? WHERE id = ?";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(1, $token);
                $update_stmt->bindParam(2, $row['id']);
                
                if ($update_stmt->execute()) {
                    http_response_code(200);
                    echo json_encode(array(
                        "message" => "Login successful.",
                        "token" => $token,
                        "user" => array(
                            "id" => $row['id'],
                            "username" => $row['username'],
                            "email" => $row['email']
                        )
                    ));
                } else {
                    http_response_code(500);
                    echo json_encode(array("message" => "Unable to generate token."));
                }
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "Invalid password."));
            }
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "User not found."));
        }
    }
}
?>
