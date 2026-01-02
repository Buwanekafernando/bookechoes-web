<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Ebook.php';

class EbookController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $item = new Ebook($this->conn);
        $stmt = $item->readAll();
        $itemCount = $stmt->rowCount();

        if ($itemCount > 0) {
            $arr = array();
            $arr["body"] = array();
            $arr["itemCount"] = $itemCount;
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
        $item = new Ebook($this->conn);
        $data = $item->readOne($id);
        if ($data != null) {
            http_response_code(200);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode("Ebook not found.");
        }
    }

    public function create() {
        $item = new Ebook($this->conn);
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->name)) {
             http_response_code(400);
             echo json_encode(array("message" => "Name is required."));
             return;
        }
        
        $createData = [
            'name' => $data->name,
            'category' => isset($data->category) ? $data->category : null,
            'description' => isset($data->description) ? $data->description : null,
            'year_of_publish' => isset($data->year_of_publish) ? $data->year_of_publish : null,
            'language' => isset($data->language) ? $data->language : null,
            'number_of_chapters' => isset($data->number_of_chapters) ? $data->number_of_chapters : null,
            'image_url' => isset($data->image_url) ? $data->image_url : null,
            'author_id' => isset($data->author_id) ? $data->author_id : null,
        ];

        if ($item->create($createData)) {
            http_response_code(201);
            echo json_encode(array("message" => "Ebook created successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Ebook could not be created."));
        }
    }

    public function update($id) {
        $item = new Ebook($this->conn);
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->name)) {
             http_response_code(400);
             echo json_encode(array("message" => "Name is required."));
             return;
        }

        $updateData = [
            'name' => $data->name,
            'category' => isset($data->category) ? $data->category : null,
            'description' => isset($data->description) ? $data->description : null,
            'year_of_publish' => isset($data->year_of_publish) ? $data->year_of_publish : null,
            'language' => isset($data->language) ? $data->language : null,
            'number_of_chapters' => isset($data->number_of_chapters) ? $data->number_of_chapters : null,
            'image_url' => isset($data->image_url) ? $data->image_url : null,
            'author_id' => isset($data->author_id) ? $data->author_id : null,
        ];

        if ($item->update($id, $updateData)) {
            http_response_code(200);
            echo json_encode(array("message" => "Ebook updated successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be updated."));
        }
    }

    public function delete($id) {
        $item = new Ebook($this->conn);
        if ($item->delete($id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Ebook deleted."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be deleted."));
        }
    }
}
?>
