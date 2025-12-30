<?php
abstract class Model {
    protected $conn;
    protected $table_name;
    protected $primary_key = "id"; // Default PK

    public function __construct($db) {
        $this->conn = $db;
    }

    // Common method to read all records
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Common method to read single record by ID
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . $this->primary_key . " = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Abstract methods to enforce CRUD implementation in children if needed, 
    // but for now we keep it flexible as parameters vary.
}
?>
