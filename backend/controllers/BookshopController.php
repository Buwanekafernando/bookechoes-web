<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Bookshop.php';

class BookshopController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Bookshop CRUD
    public function getAll() {
        $item = new Bookshop($this->conn);
        $stmt = $item->readAll();
        $itemCount = $stmt->rowCount();

        if ($itemCount > 0) {
            $arr = array("body" => array(), "itemCount" => $itemCount);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($arr["body"], $row);
            }
            echo json_encode($arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No record found."));
        }
    }

    public function getOne($id) {
        $item = new Bookshop($this->conn);
        $data = $item->readOne($id);
        if ($data != null) {
            http_response_code(200);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode("Bookshop not found.");
        }
    }

    public function create() {
        $item = new Bookshop($this->conn);
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->name)) {
             http_response_code(400);
             echo json_encode(array("message" => "Name is required."));
             return;
        }

        $createData = [
            'name' => $data->name,
            'location' => isset($data->location) ? $data->location : null,
            'country' => isset($data->country) ? $data->country : null,
        ];

        if ($item->create($createData)) {
            http_response_code(201);
            echo json_encode(array("message" => "Bookshop created successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Bookshop could not be created."));
        }
    }

    public function update($id) {
        $item = new Bookshop($this->conn);
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->name)) {
             http_response_code(400);
             echo json_encode(array("message" => "Name is required."));
             return;
        }

        $updateData = [
            'name' => $data->name,
            'location' => isset($data->location) ? $data->location : null,
            'country' => isset($data->country) ? $data->country : null,
        ];

        if ($item->update($id, $updateData)) {
            http_response_code(200);
            echo json_encode(array("message" => "Bookshop updated successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be updated."));
        }
    }

    public function delete($id) {
        $item = new Bookshop($this->conn);
        if ($item->delete($id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Bookshop deleted."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be deleted."));
        }
    }

    // Inventory Methods
    public function getInventory($bookshop_id) {
        $item = new Bookshop($this->conn);
        $stmt = $item->getInventory($bookshop_id);
        $itemCount = $stmt->rowCount();

        $arr = array("body" => array(), "itemCount" => $itemCount);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($arr["body"], $row);
        }
        echo json_encode($arr);
    }

    public function addInventory($bookshop_id) {
        $item = new Bookshop($this->conn);
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->book_id) || !isset($data->stock_quantity) || !isset($data->price)) {
            http_response_code(400);
            echo json_encode(array("message" => "Incomplete data. need book_id, stock_quantity, price"));
            return;
        }

        if ($item->addInventory($bookshop_id, $data->book_id, $data->stock_quantity, $data->price)) {
            http_response_code(201);
            echo json_encode(array("message" => "Inventory added."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Could not add inventory."));
        }
    }
    
    public function updateInventory($bookshop_id, $book_id) {
        $item = new Bookshop($this->conn);
        $data = json_decode(file_get_contents("php://input"));
         
        // book_id is passed in URL usually for individual item update?
        // Method signature: updateInventory($bookshop_id, $book_id, $stock, $price)
        
        if (!isset($data->stock_quantity) || !isset($data->price)) {
            http_response_code(400);
            echo json_encode(array("message" => "Incomplete data. Need stock_quantity, price."));
            return;
        }

        if ($item->updateInventory($bookshop_id, $book_id, $data->stock_quantity, $data->price)) {
            http_response_code(200);
            echo json_encode(array("message" => "Inventory updated."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Could not update inventory."));
        }
    }

    public function removeInventory($bookshop_id, $book_id) {
        $item = new Bookshop($this->conn);
        if ($item->removeInventory($bookshop_id, $book_id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Inventory removed."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Could not remove inventory."));
        }
    }
}
?>
