<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Publisher.php';

class PublisherController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $item = new Publisher($this->conn);
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
        $item = new Publisher($this->conn);
        $data = $item->readOne($id);
        if ($data != null) {
            http_response_code(200);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode("Publisher not found.");
        }
    }

    public function create() {
        $item = new Publisher($this->conn);
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->name)) {
             http_response_code(400);
             echo json_encode(array("message" => "Name is required."));
             return;
        }

        $createData = [
            'name' => $data->name,
            'country' => isset($data->country) ? $data->country : null,
            'website_url' => isset($data->website_url) ? $data->website_url : null,
        ];

        if ($item->create($createData)) {
            http_response_code(201);
            echo json_encode(array("message" => "Publisher created successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Publisher could not be created."));
        }
    }

    public function update($id) {
        $item = new Publisher($this->conn);
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->name)) {
             http_response_code(400);
             echo json_encode(array("message" => "Name is required."));
             return;
        }

        $updateData = [
            'name' => $data->name,
            'country' => isset($data->country) ? $data->country : null,
            'website_url' => isset($data->website_url) ? $data->website_url : null,
        ];

        if ($item->update($id, $updateData)) {
            http_response_code(200);
            echo json_encode(array("message" => "Publisher updated successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be updated."));
        }
    }

    public function delete($id) {
        $item = new Publisher($this->conn);
        if ($item->delete($id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Publisher deleted."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be deleted."));
        }
    }
}
?>
