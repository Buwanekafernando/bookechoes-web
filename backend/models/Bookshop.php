<?php
require_once __DIR__ . '/Model.php';

class Bookshop extends Model {
    protected $table_name = "bookshops";
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

    // Inventory Management
    public function getInventory($bookshop_id) {
        $query = "SELECT b.title, bb.book_id, bb.stock_quantity, bb.price 
                  FROM bookshop_books bb
                  JOIN book b ON bb.book_id = b.book_id
                  WHERE bb.bookshop_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$bookshop_id]);
        return $stmt;
    }

    public function addInventory($bookshop_id, $book_id, $stock, $price) {
        $query = "INSERT INTO bookshop_books (bookshop_id, book_id, stock_quantity, price) 
                  VALUES (?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE stock_quantity = VALUES(stock_quantity), price = VALUES(price)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$bookshop_id, $book_id, $stock, $price]);
    }

    public function updateInventory($bookshop_id, $book_id, $stock, $price) {
        $query = "UPDATE bookshop_books 
                  SET stock_quantity = ?, price = ? 
                  WHERE bookshop_id = ? AND book_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$stock, $price, $bookshop_id, $book_id]);
    }

    public function removeInventory($bookshop_id, $book_id) {
        $query = "DELETE FROM bookshop_books 
                  WHERE bookshop_id = ? AND book_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$bookshop_id, $book_id]);
    }
}
?>
