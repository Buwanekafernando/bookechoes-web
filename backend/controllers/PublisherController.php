<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Publisher.php';
require_once __DIR__ . '/../utils/Auth.php';

class PublisherController extends Controller {
    private $publisher;

    public function __construct($db) {
        parent::__construct($db);
        $this->publisher = new Publisher($db);
    }

    public function getAll() {
        $stmt = $this->publisher->readAll();
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
        $data = $this->publisher->readOne($id);
        if ($data != null) {
            http_response_code(200);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Publisher not found."));
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

        if ($this->publisher->create($data)) {
            http_response_code(201);
            echo json_encode(array("message" => "Publisher created successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Publisher could not be created."));
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

        if ($this->publisher->update($id, $data)) {
            http_response_code(200);
            echo json_encode(array("message" => "Publisher updated successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be updated."));
        }
    }

    public function delete($id) {
        Auth::validateToken();
        if ($this->publisher->delete($id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Publisher deleted."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be deleted."));
        }
    }
}
?>
