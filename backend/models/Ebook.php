<?php
require_once __DIR__ . '/Model.php';

class Ebook extends Model {
    protected $table_name = "Ebook";
    protected $primary_key = "ebook_id";

    public function readAll() {
        // Ebook has author_id directly now? Use user provided schema:
        // Ebook- ebook_id, name, category, description, year_of_publish, language, number_of_chapters, image_url, author_id
        // Wait, NO book_id linking to Books table?
        // User schema implies Ebook is a standalone entity or duplicates data, OR `name` is title.
        // It has `author_id`. It doesn't seem to link to `Book`. It seems independent.
        // I will follow the schema literally.
        
        $query = "SELECT e.*, a.name as author_name
                  FROM " . $this->table_name . " e
                  LEFT JOIN Author a ON e.author_id = a.author_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "SELECT e.*, a.name as author_name
                  FROM " . $this->table_name . " e
                  LEFT JOIN Author a ON e.author_id = a.author_id
                  WHERE e." . $this->primary_key . " = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, category, description, year_of_publish, language, number_of_chapters, image_url, author_id) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $data['name']);
        $stmt->bindParam(2, $data['category']);
        $stmt->bindParam(3, $data['description']);
        $stmt->bindParam(4, $data['year_of_publish']);
        $stmt->bindParam(5, $data['language']);
        $stmt->bindParam(6, $data['number_of_chapters']);
        $stmt->bindParam(7, $data['image_url']);
        $stmt->bindParam(8, $data['author_id']);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = ?, category = ?, description = ?, year_of_publish = ?, language = ?, number_of_chapters = ?, image_url = ?, author_id = ? 
                  WHERE " . $this->primary_key . " = ?";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $data['name']);
        $stmt->bindParam(2, $data['category']);
        $stmt->bindParam(3, $data['description']);
        $stmt->bindParam(4, $data['year_of_publish']);
        $stmt->bindParam(5, $data['language']);
        $stmt->bindParam(6, $data['number_of_chapters']);
        $stmt->bindParam(7, $data['image_url']);
        $stmt->bindParam(8, $data['author_id']);
        $stmt->bindParam(9, $id);

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
