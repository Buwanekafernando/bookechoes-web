<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Ebook.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Auth.php';

class EbookController extends Controller {
    private $ebook;

    public function __construct($db) {
        parent::__construct($db);
        $this->ebook = new Ebook($db);
    }

    public function getAll() {
        $stmt = $this->ebook->readAll();
        $ebooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Response::success("Ebooks fetched successfully", $ebooks);
    }

    public function getOne($id) {
        if (!$id) Response::error("ID is required");
        $ebook = $this->ebook->readOne($id);
        if ($ebook) {
            Response::success("Ebook fetched successfully", $ebook);
        } else {
            Response::error("Ebook not found", 404);
        }
    }

    public function create() {
        Auth::validateToken();
        $data = $this->getInput();
        // Required: name, author_id
        $errors = Validator::validateRequired($data, ['name', 'author_id']);

        if (!empty($errors)) {
            Response::error("Validation Error", 400, $errors);
        }
        $data = Validator::sanitize($data);

        $fields = ['category', 'description', 'year_of_publish', 'language', 'number_of_chapters', 'image_url'];
        foreach ($fields as $field) {
            if (!isset($data[$field])) $data[$field] = null;
        }

        if ($this->ebook->create($data)) {
            Response::success("Ebook created successfully", null, 201);
        } else {
            Response::error("Unable to create ebook", 503);
        }
    }

    public function update($id) {
        Auth::validateToken();
        if (!$id) Response::error("ID is required");
        $data = $this->getInput();
        $data = Validator::sanitize($data);
        
        $existing = $this->ebook->readOne($id);
        if (!$existing) Response::error("Ebook not found", 404);

        $fields = ['name', 'category', 'description', 'year_of_publish', 'language', 'number_of_chapters', 'image_url', 'author_id'];
        foreach ($fields as $field) {
            if (!isset($data[$field])) $data[$field] = $existing[$field];
        }

        if ($this->ebook->update($id, $data)) {
            Response::success("Ebook updated successfully");
        } else {
            Response::error("Unable to update ebook", 503);
        }
    }

    public function delete($id) {
        Auth::validateToken();
        if (!$id) Response::error("ID is required");

        if ($this->ebook->delete($id)) {
            Response::success("Ebook deleted successfully");
        } else {
            Response::error("Unable to delete ebook", 503);
        }
    }
}
?>
