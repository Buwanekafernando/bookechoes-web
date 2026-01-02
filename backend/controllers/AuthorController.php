<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Author.php';

class AuthorController {
    private $conn;
    private $db_table = "Author";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $item = new Author($this->conn);
        $stmt = $item->readAll();
        $itemCount = $stmt->rowCount();

        if ($itemCount > 0) {
            $authorArr = array();
            $authorArr["body"] = array();
            $authorArr["itemCount"] = $itemCount;

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $e = array(
                    "author_id" => $author_id,
                    "name" => $name,
                    "country" => $country,
                    "no_of_books_published" => $no_of_books_published,
                    "about" => $about,
                    "website_url" => $website_url,
                    "socialmedia_url" => $socialmedia_url,
                    "image_url" => $image_url
                );
                array_push($authorArr["body"], $e);
            }
            echo json_encode($authorArr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No record found."));
        }
    }

    public function getOne($id) {
        $item = new Author($this->conn);
        $date = $item->readOne($id);

        if ($date != null) {
            // Create array
            $emp_arr = array(
                "author_id" =>  $date['author_id'],
                "name" => $date['name'],
                "country" => $date['country'],
                "no_of_books_published" => $date['no_of_books_published'],
                "about" => $date['about'],
                "website_url" => $date['website_url'],
                "socialmedia_url" => $date['socialmedia_url'],
                "image_url" => $date['image_url']
            );
            http_response_code(200);
            echo json_encode($emp_arr);
        } else {
            http_response_code(404);
            echo json_encode("Author not found.");
        }
    }

    public function create() {
        $item = new Author($this->conn);
        $data = json_decode(file_get_contents("php://input"));
        
        // Basic validation
        if (empty($data->name)) {
             http_response_code(400);
             echo json_encode(array("message" => "Name is required."));
             return;
        }

        // Mapping
        $authorData = [
            'name' => $data->name,
            'country' => isset($data->country) ? $data->country : null,
            'no_of_books_published' => isset($data->no_of_books_published) ? $data->no_of_books_published : 0,
            'about' => isset($data->about) ? $data->about : null,
            'website_url' => isset($data->website_url) ? $data->website_url : null,
            'socialmedia_url' => isset($data->socialmedia_url) ? $data->socialmedia_url : null,
            'image_url' => isset($data->image_url) ? $data->image_url : null,
        ];

        if ($item->create($authorData)) {
            http_response_code(201);
            echo json_encode(array("message" => "Author created successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Author could not be created."));
        }
    }

    public function update($id) {
        $item = new Author($this->conn);
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->name)) {
             http_response_code(400);
             echo json_encode(array("message" => "Name is required."));
             return;
        }

        $authorData = [
            'name' => $data->name,
            'country' => isset($data->country) ? $data->country : null,
            'no_of_books_published' => isset($data->no_of_books_published) ? $data->no_of_books_published : 0,
            'about' => isset($data->about) ? $data->about : null,
            'website_url' => isset($data->website_url) ? $data->website_url : null,
            'socialmedia_url' => isset($data->socialmedia_url) ? $data->socialmedia_url : null,
            'image_url' => isset($data->image_url) ? $data->image_url : null,
        ];

        if ($item->update($id, $authorData)) {
            http_response_code(200);
            echo json_encode(array("message" => "Author updated successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be updated."));
        }
    }

    public function delete($id) {
        $item = new Author($this->conn);
        
        if ($item->delete($id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Author deleted."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Data could not be deleted."));
        }
    }
}
?>
