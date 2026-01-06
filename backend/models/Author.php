<?php
require_once __DIR__ . '/Model.php';

class Author extends Model {
    protected $table_name = "Author"; // User specified table name (Capitalized?) I'll use exactly what they said if case sensitive, but usually lowercase in code. Let's assume lowercase for safety unless Windows. User wrote "Author". I'll try to stick to "authors" if they just meant the entity, but "Author" implies table name. I will check previous create table. The user said "these are the table structure i have created". I should use their casing if possible or standard. I will use 'authors' assuming they mapped it standardly, but if they strictly want 'Author', I might need to check. Let's stick to standard lowercase table names 'authors' unless I get an error, but actually I should update to their column names first. 
    // Wait, user wrote "Author - ...". I'll assume table name is `Author`.
    protected $table_name_s = "Author"; // Just to be safe? No, let's look at the previous setup_db. It used `authors`. I will update to `Author` to match their description perfectly? 
    // "these are the table sturcture i have created in the phpmyadmin... Author"
    // Okay, I will use `Author` as table name.
    
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
