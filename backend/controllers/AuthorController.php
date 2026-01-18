<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Author.php';
require_once __DIR__ . '/../utils/Auth.php';

class AuthorController extends Controller {
    private $author;

    public function __construct($db) {
        parent::__construct($db);
        $this->author = new Author($db);
    }

    public function getAll() {
        $stmt = $this->author->readAll();
        $itemCount = $stmt->rowCount();

        if ($itemCount > 0) {
            $authorArr = array("body" => array(), "itemCount" => $itemCount);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($authorArr["body"], $row);
            }
            echo json_encode($authorArr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No record found."));
        }
    }

    public function getOne($id) {
        $data = $this->author->readOne($id);
        if ($data != null) {
            http_response_code(200);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Author not found."));
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

        if ($this->author->create($data)) {
            http_response_code(201);
            echo json_encode(array("message" => "Author created successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Author could not be created."));
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

        if ($this->author->update($id, $data)) {
            http_response_code(200);
            echo json_encode(array("message" => "Author updated successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be updated."));
        }
    }

    public function delete($id) {
        Auth::validateToken();
        if ($this->author->delete($id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Author deleted."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be deleted."));
        }
    }
}
?>
