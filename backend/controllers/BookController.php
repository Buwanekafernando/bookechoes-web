<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../utils/Auth.php';

class BookController extends Controller {
    private $book;

    public function __construct($db) {
        parent::__construct($db);
        $this->book = new Book($db);
    }

    public function getAll() {
        $stmt = $this->book->readAll();
        $itemCount = $stmt->rowCount();

        if ($itemCount > 0) {
            $bookArr = array("body" => array(), "itemCount" => $itemCount);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($bookArr["body"], $row);
            }
            echo json_encode($bookArr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No record found."));
        }
    }

    public function getOne($id) {
        $data = $this->book->readOne($id);
        if ($data != null) {
            http_response_code(200);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Book not found."));
        }
    }

    public function create() {
        Auth::validateToken();
        $data = $this->getInput();
        
        if (empty($data['title'])) {
             http_response_code(400);
             echo json_encode(array("message" => "Title is required."));
             return;
        }

        if ($this->book->create($data)) {
            http_response_code(201);
            echo json_encode(array("message" => "Book created successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Book could not be created."));
        }
    }

    public function update($id) {
        Auth::validateToken();
        $data = $this->getInput();

        if (empty($data['title'])) {
             http_response_code(400);
             echo json_encode(array("message" => "Title is required."));
             return;
        }

        if ($this->book->update($id, $data)) {
            http_response_code(200);
            echo json_encode(array("message" => "Book updated successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be updated."));
        }
    }

    public function delete($id) {
        Auth::validateToken();
        if ($this->book->delete($id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Book deleted."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be deleted."));
        }
    }
}
?>
