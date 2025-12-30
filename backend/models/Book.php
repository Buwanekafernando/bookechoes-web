<?php
require_once __DIR__ . '/Model.php';

class Book extends Model {
    protected $table_name = "Book";
    protected $primary_key = "book_id";

    // Override readAll to include Author and Publisher names
    // Note: Author table is "Author", Publisher is "Publisher"
    public function readAll() {
        $query = "SELECT b.*, a.name as author_name, p.name as publisher_name 
                  FROM " . $this->table_name . " b
                  LEFT JOIN Author a ON b.author_id = a.author_id
                  LEFT JOIN Publisher p ON b.publisher_id = p.publisher_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "SELECT b.*, a.name as author_name, p.name as publisher_name 
                  FROM " . $this->table_name . " b
                  LEFT JOIN Author a ON b.author_id = a.author_id
                  LEFT JOIN Publisher p ON b.publisher_id = p.publisher_id
                  WHERE b." . $this->primary_key . " = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (title, category, description, year_of_publish, number_of_chapters, language, image_url, publisher_id, author_id) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $data['title']);
        $stmt->bindParam(2, $data['category']);
        $stmt->bindParam(3, $data['description']);
        $stmt->bindParam(4, $data['year_of_publish']);
        $stmt->bindParam(5, $data['number_of_chapters']);
        $stmt->bindParam(6, $data['language']);
        $stmt->bindParam(7, $data['image_url']);
        $stmt->bindParam(8, $data['publisher_id']);
        $stmt->bindParam(9, $data['author_id']);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET title = ?, category = ?, description = ?, year_of_publish = ?, number_of_chapters = ?, language = ?, image_url = ?, publisher_id = ?, author_id = ? 
                  WHERE " . $this->primary_key . " = ?";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $data['title']);
        $stmt->bindParam(2, $data['category']);
        $stmt->bindParam(3, $data['description']);
        $stmt->bindParam(4, $data['year_of_publish']);
        $stmt->bindParam(5, $data['number_of_chapters']);
        $stmt->bindParam(6, $data['language']);
        $stmt->bindParam(7, $data['image_url']);
        $stmt->bindParam(8, $data['publisher_id']);
        $stmt->bindParam(9, $data['author_id']);
        $stmt->bindParam(10, $id);

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
