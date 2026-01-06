<?php
require_once __DIR__ . '/Model.php';

class Bookshop extends Model {
    protected $table_name = "Bookshops";
    protected $primary_key = "bookshop_id";

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, location, country, description, image_url, status) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $data['name']);
        $stmt->bindParam(2, $data['location']);
        $stmt->bindParam(3, $data['country']);
        $stmt->bindParam(4, $data['description']);
        $stmt->bindParam(5, $data['image_url']);
        $status = $data['status'] ?? 'published';
        $stmt->bindParam(6, $status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = ?, location = ?, country = ?, description = ?, image_url = ?, status = ? 
                  WHERE " . $this->primary_key . " = ?";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $data['name']);
        $stmt->bindParam(2, $data['location']);
        $stmt->bindParam(3, $data['country']);
        $stmt->bindParam(4, $data['description']);
        $stmt->bindParam(5, $data['image_url']);
        $status = $data['status'] ?? 'published';
        $stmt->bindParam(6, $status);
        $stmt->bindParam(7, $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE " . $this->primary_key . " = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
