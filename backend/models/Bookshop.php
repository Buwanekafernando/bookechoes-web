<?php
require_once __DIR__ . '/Model.php';

class Bookshop extends Model {
    protected $table_name = "Bookshops";
    protected $primary_key = "bookshop_id";

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " (name, location, country) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $data['name']);
        $stmt->bindParam(2, $data['location']);
        $stmt->bindParam(3, $data['country']);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET name = ?, location = ?, country = ? WHERE " . $this->primary_key . " = ?";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $data['name']);
        $stmt->bindParam(2, $data['location']);
        $stmt->bindParam(3, $data['country']);
        $stmt->bindParam(4, $id);

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
    
    // Inventory Management Methods
    // Table: Bookshop_Books (bookshop_id, book_id, stock_quantity, price)
    // Note: No 'id' PK mentioned by user. Assuming composite key (bookshop_id, book_id) 
    // OR just inserting/deleting by pair.
    // If I need to update, I'll update by pair.
    
    public function getInventory($bookshop_id) {
        $query = "SELECT bb.*, b.title 
                  FROM Bookshop_Books bb
                  JOIN Book b ON bb.book_id = b.book_id
                  WHERE bb.bookshop_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $bookshop_id);
        $stmt->execute();
        return $stmt;
    }
    
    public function addInventory($bookshop_id, $book_id, $stock, $price) {
        // Check if exists first? Or Insert on duplicate update?
        // Let's assume unique constraint on pair.
        // However, standard insert for now.
        $query = "INSERT INTO Bookshop_Books (bookshop_id, book_id, stock_quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $bookshop_id);
        $stmt->bindParam(2, $book_id);
        $stmt->bindParam(3, $stock);
        $stmt->bindParam(4, $price);
        return $stmt->execute();
    }
    
    public function updateInventory($bookshop_id, $book_id, $stock, $price) {
        // Since no simple 'id' provided in description, updating by composite key.
        $query = "UPDATE Bookshop_Books SET stock_quantity = ?, price = ? WHERE bookshop_id = ? AND book_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $stock);
        $stmt->bindParam(2, $price);
        $stmt->bindParam(3, $bookshop_id);
        $stmt->bindParam(4, $book_id);
        return $stmt->execute();
    }
    
    public function removeInventory($bookshop_id, $book_id) {
        $query = "DELETE FROM Bookshop_Books WHERE bookshop_id = ? AND book_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $bookshop_id);
        $stmt->bindParam(2, $book_id);
        return $stmt->execute();
    }
}
?>
