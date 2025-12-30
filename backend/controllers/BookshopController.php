<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Bookshop.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Auth.php';

class BookshopController extends Controller {
    private $bookshop;

    public function __construct($db) {
        parent::__construct($db);
        $this->bookshop = new Bookshop($db);
    }

    public function getAll() {
        $stmt = $this->bookshop->readAll();
        $bookshops = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Response::success("Bookshops fetched successfully", $bookshops);
    }

    public function getOne($id) {
        if (!$id) Response::error("ID is required");
        $bookshop = $this->bookshop->readOne($id);
        if ($bookshop) {
            Response::success("Bookshop fetched successfully", $bookshop);
        } else {
            Response::error("Bookshop not found", 404);
        }
    }

    public function create() {
        Auth::validateToken();
        $data = $this->getInput();
        $errors = Validator::validateRequired($data, ['name', 'location']);

        if (!empty($errors)) {
            Response::error("Validation Error", 400, $errors);
        }
        $data = Validator::sanitize($data);

        if (!isset($data['country'])) $data['country'] = null;

        if ($this->bookshop->create($data)) {
            Response::success("Bookshop created successfully", null, 201);
        } else {
            Response::error("Unable to create bookshop", 503);
        }
    }

    public function update($id) {
        Auth::validateToken();
        if (!$id) Response::error("ID is required");
        $data = $this->getInput();
        $data = Validator::sanitize($data);

        $existing = $this->bookshop->readOne($id);
        if (!$existing) Response::error("Bookshop not found", 404);
        
        $fields = ['name', 'location', 'country'];
        foreach ($fields as $field) {
            if (!isset($data[$field])) $data[$field] = $existing[$field];
        }

        if ($this->bookshop->update($id, $data)) {
            Response::success("Bookshop updated successfully");
        } else {
            Response::error("Unable to update bookshop", 503);
        }
    }

    public function delete($id) {
        Auth::validateToken();
        if (!$id) Response::error("ID is required");

        if ($this->bookshop->delete($id)) {
            Response::success("Bookshop deleted successfully");
        } else {
            Response::error("Unable to delete bookshop", 503);
        }
    }

    public function getInventory($id) {
        if (!$id) Response::error("Bookshop ID is required");
        $stmt = $this->bookshop->getInventory($id);
        $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Response::success("Inventory fetched successfully", $inventory);
    }
    
    public function addInventory($id) {
        Auth::validateToken();
        if (!$id) Response::error("Bookshop ID is required");
        $data = $this->getInput();
        // Updated requirement: stock_quantity, price
        $errors = Validator::validateRequired($data, ['book_id', 'stock_quantity', 'price']);
        
        if (!empty($errors)) {
            Response::error("Validation Error", 400, $errors);
        }
        
        if ($this->bookshop->addInventory($id, $data['book_id'], $data['stock_quantity'], $data['price'])) {
            Response::success("Inventory added successfully", null, 201);
        } else {
            Response::error("Unable to add inventory", 503);
        }
    }
    
    public function updateInventory() {
        Auth::validateToken();
        $data = $this->getInput();
        // Since no inventory_id passed, we need bookshop_id and book_id
        // We can pass them in body or query. Let's assume body to be consistent with add.
        // Or params: ?id=SHOP_ID&book_id=BOOK_ID
        // My router passes `id` (shop id). We need `book_id`.
        
        $bookshop_id = isset($_GET['id']) ? $_GET['id'] : null;
        $book_id = isset($_GET['book_id']) ? $_GET['book_id'] : (isset($data['book_id']) ? $data['book_id'] : null);
        
        if (!$bookshop_id || !$book_id) Response::error("Bookshop ID and Book ID required");
        
        $errors = Validator::validateRequired($data, ['stock_quantity', 'price']);
        if (!empty($errors)) Response::error("Validation Error", 400, $errors);

        if ($this->bookshop->updateInventory($bookshop_id, $book_id, $data['stock_quantity'], $data['price'])) {
            Response::success("Inventory updated successfully");
        } else {
             Response::error("Unable to update inventory", 503);
        }
    }
    
    public function removeInventory() {
        Auth::validateToken();
        // Need bookshop_id ($id from router) and book_id
        $bookshop_id = isset($_GET['id']) ? $_GET['id'] : null;
        $book_id = isset($_GET['book_id']) ? $_GET['book_id'] : null;
        
        if (!$bookshop_id || !$book_id) Response::error("Bookshop ID and Book ID required");
        
        if ($this->bookshop->removeInventory($bookshop_id, $book_id)) {
            Response::success("Inventory removed successfully");
        } else {
             Response::error("Unable to remove inventory", 503);
        }
    }
}
?>
