<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Book.php';

class BookController {
    private $conn;
    private $db_table = "Book";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $item = new Book($this->conn);
        $stmt = $item->readAll();
        $itemCount = $stmt->rowCount();

        if ($itemCount > 0) {
            $bookArr = array();
            $bookArr["body"] = array();
            $bookArr["itemCount"] = $itemCount;

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Manually extracting or just pushing the row
                // Since readAll returns author_name and publisher_name, we can just push row
                array_push($bookArr["body"], $row);
            }
            echo json_encode($bookArr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No record found."));
        }
    }

    public function getOne($id) {
        $item = new Book($this->conn);
        $data = $item->readOne($id);

        if ($data != null) {
            http_response_code(200);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode("Book not found.");
        }
    }

    public function create() {
        $item = new Book($this->conn);
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->title)) {
             http_response_code(400);
             echo json_encode(array("message" => "Title is required."));
             return;
        }

        // Map data
        $bookData = [
            'title' => $data->title,
            'category' => isset($data->category) ? $data->category : null,
            'description' => isset($data->description) ? $data->description : null,
            'year_of_publish' => isset($data->year_of_publish) ? $data->year_of_publish : null,
            'number_of_chapters' => isset($data->number_of_chapters) ? $data->number_of_chapters : null,
            'language' => isset($data->language) ? $data->language : null,
            'image_url' => isset($data->image_url) ? $data->image_url : null,
            'publisher_id' => isset($data->publisher_id) ? $data->publisher_id : null,
            'author_id' => isset($data->author_id) ? $data->author_id : null,
        ];

        if ($item->create($bookData)) {
            http_response_code(201);
            echo json_encode(array("message" => "Book created successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Book could not be created."));
        }
    }

    public function update($id) {
        $item = new Book($this->conn);
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->title)) {
             http_response_code(400);
             echo json_encode(array("message" => "Title is required."));
             return;
        }
        
        $bookData = [
            'title' => $data->title,
            'category' => isset($data->category) ? $data->category : null,
            'description' => isset($data->description) ? $data->description : null,
            'year_of_publish' => isset($data->year_of_publish) ? $data->year_of_publish : null,
            'number_of_chapters' => isset($data->number_of_chapters) ? $data->number_of_chapters : null,
            'language' => isset($data->language) ? $data->language : null,
            'image_url' => isset($data->image_url) ? $data->image_url : null,
            'publisher_id' => isset($data->publisher_id) ? $data->publisher_id : null,
            'author_id' => isset($data->author_id) ? $data->author_id : null,
        ];

        if ($item->update($id, $bookData)) {
            http_response_code(200);
            echo json_encode(array("message" => "Book updated successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be updated."));
        }
    }

    public function delete($id) {
        $item = new Book($this->conn);
        if ($item->delete($id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Book deleted."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be deleted."));
        }
    }
}
?>
