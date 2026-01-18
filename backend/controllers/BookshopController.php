<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Bookshop.php';
require_once __DIR__ . '/../utils/Auth.php';

class BookshopController extends Controller {
    private $bookshop;

    public function __construct($db) {
        parent::__construct($db);
        $this->bookshop = new Bookshop($db);
    }

    // Bookshop CRUD
    public function getAll() {
        $stmt = $this->bookshop->readAll();
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
        $data = $this->bookshop->readOne($id);
        if ($data != null) {
            http_response_code(200);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Bookshop not found."));
        }
    }

    public function create() {
        Auth::validateToken();
        $data = $this->getInput();
        
        if (empty($data['name'])) {
             http_response_code(400);
             echo json_encode(array("message" => "Name is required."));
             return;
        }

        if ($this->bookshop->create($data)) {
            http_response_code(201);
            echo json_encode(array("message" => "Bookshop created successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Bookshop could not be created."));
        }
    }

    public function update($id) {
        Auth::validateToken();
        $data = $this->getInput();

        if (empty($data['name'])) {
             http_response_code(400);
             echo json_encode(array("message" => "Name is required."));
             return;
        }

        if ($this->bookshop->update($id, $data)) {
            http_response_code(200);
            echo json_encode(array("message" => "Bookshop updated successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be updated."));
        }
    }

    public function delete($id) {
        Auth::validateToken();
        if ($this->bookshop->delete($id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Bookshop deleted."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be deleted."));
        }
    }

    // Inventory Methods
    public function getInventory($bookshop_id) {
        $stmt = $this->bookshop->getInventory($bookshop_id);
        $itemCount = $stmt->rowCount();

        $arr = array("body" => array(), "itemCount" => $itemCount);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($arr["body"], $row);
        }
        echo json_encode($arr);
    }

    public function addInventory($bookshop_id) {
        Auth::validateToken();
        $data = (object)$this->getInput();

        if (!isset($data->book_id) || !isset($data->stock_quantity) || !isset($data->price)) {
            http_response_code(400);
            echo json_encode(array("message" => "Incomplete data. need book_id, stock_quantity, price"));
            return;
        }

        if ($this->bookshop->addInventory($bookshop_id, $data->book_id, $data->stock_quantity, $data->price)) {
            http_response_code(201);
            echo json_encode(array("message" => "Inventory added."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Could not add inventory."));
        }
    }
    
    public function updateInventory($bookshop_id, $book_id) {
        Auth::validateToken();
        $data = (object)$this->getInput();
        
        if (!isset($data->stock_quantity) || !isset($data->price)) {
            http_response_code(400);
            echo json_encode(array("message" => "Incomplete data. Need stock_quantity, price."));
            return;
        }

        if ($this->bookshop->updateInventory($bookshop_id, $book_id, $data->stock_quantity, $data->price)) {
            http_response_code(200);
            echo json_encode(array("message" => "Inventory updated."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Could not update inventory."));
        }
    }

    public function removeInventory($bookshop_id, $book_id) {
        Auth::validateToken();
        if ($this->bookshop->removeInventory($bookshop_id, $book_id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Inventory removed."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Could not remove inventory."));
        }
    }
}
?>
