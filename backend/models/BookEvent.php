<?php
require_once __DIR__ . '/Model.php';

class BookEvent extends Model {
    protected $table_name = "Book_Events";
    protected $primary_key = "book_event_id";

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " (name, location, date_start, date_end) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $data['name']);
        $stmt->bindParam(2, $data['location']);
        $stmt->bindParam(3, $data['date_start']);
        $stmt->bindParam(4, $data['date_end']);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET name = ?, location = ?, date_start = ?, date_end = ? WHERE " . $this->primary_key . " = ?";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $data['name']);
        $stmt->bindParam(2, $data['location']);
        $stmt->bindParam(3, $data['date_start']);
        $stmt->bindParam(4, $data['date_end']);
        $stmt->bindParam(5, $id);

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
