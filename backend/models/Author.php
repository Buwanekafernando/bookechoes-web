<?php
require_once __DIR__ . '/Model.php';

class Author extends Model {
    protected $table_name = "author";
    protected $primary_key = "author_id";

    // Create
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, country, no_of_books_published, about, website_url, socialmedia_url, image_url, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $data['name']);
        $stmt->bindParam(2, $data['country']);
        $stmt->bindParam(3, $data['no_of_books_published']);
        $stmt->bindParam(4, $data['about']);
        $stmt->bindParam(5, $data['website_url']);
        $stmt->bindParam(6, $data['socialmedia_url']);
        $stmt->bindParam(7, $data['image_url']);
        $status = $data['status'] ?? 'published';
        $stmt->bindParam(8, $status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = ?, country = ?, no_of_books_published = ?, about = ?, website_url = ?, socialmedia_url = ?, image_url = ?, status = ? 
                  WHERE " . $this->primary_key . " = ?";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $data['name']);
        $stmt->bindParam(2, $data['country']);
        $stmt->bindParam(3, $data['no_of_books_published']);
        $stmt->bindParam(4, $data['about']);
        $stmt->bindParam(5, $data['website_url']);
        $stmt->bindParam(6, $data['socialmedia_url']);
        $stmt->bindParam(7, $data['image_url']);
        $status = $data['status'] ?? 'published';
        $stmt->bindParam(8, $status);
        $stmt->bindParam(9, $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete
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
