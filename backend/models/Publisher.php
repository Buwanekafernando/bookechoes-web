<?php
require_once __DIR__ . '/Model.php';

class Publisher extends Model {
    protected $table_name = "Publisher";
    protected $primary_key = "publisher_id";

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " (name, country, website_url, image_url, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $data['name']);
        $stmt->bindParam(2, $data['country']);
        $stmt->bindParam(3, $data['website_url']);
        $stmt->bindParam(4, $data['image_url']);
        $status = $data['status'] ?? 'published';
        $stmt->bindParam(5, $status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET name = ?, country = ?, website_url = ?, image_url = ?, status = ? WHERE " . $this->primary_key . " = ?";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $data['name']);
        $stmt->bindParam(2, $data['country']);
        $stmt->bindParam(3, $data['website_url']);
        $stmt->bindParam(4, $data['image_url']);
        $status = $data['status'] ?? 'published';
        $stmt->bindParam(5, $status);
        $stmt->bindParam(6, $id);

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
