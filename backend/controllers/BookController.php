<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Auth.php';

class BookController extends Controller {
    private $book;

    public function __construct($db) {
        parent::__construct($db);
        $this->book = new Book($db);
    }

    public function getAll() {
        $stmt = $this->book->readAll();
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Response::success("Books fetched successfully", $books);
    }

    public function getOne($id) {
        if (!$id) Response::error("ID is required");
        $book = $this->book->readOne($id);
        if ($book) {
            Response::success("Book fetched successfully", $book);
        } else {
            Response::error("Book not found", 404);
        }
    }

    public function create() {
        Auth::validateToken();
        $data = $this->getInput();
        // Validation: what is absolutely required? title, probably links.
        $errors = Validator::validateRequired($data, ['title', 'author_id', 'publisher_id']);

        if (!empty($errors)) {
            Response::error("Validation Error", 400, $errors);
        }
        $data = Validator::sanitize($data);

        // Fill optional
        $fields = ['category', 'description', 'year_of_publish', 'number_of_chapters', 'language', 'image_url'];
        foreach ($fields as $field) {
            if (!isset($data[$field])) $data[$field] = null;
        }

        if ($this->book->create($data)) {
            Response::success("Book created successfully", null, 201);
        } else {
            Response::error("Unable to create book", 503);
        }
    }

    public function update($id) {
        Auth::validateToken();
        if (!$id) Response::error("ID is required");
        $data = $this->getInput();
        $data = Validator::sanitize($data);
        
        $existing = $this->book->readOne($id);
        if (!$existing) Response::error("Book not found", 404);
        
        $fields = ['title', 'category', 'description', 'year_of_publish', 'number_of_chapters', 'language', 'image_url', 'publisher_id', 'author_id'];
        foreach ($fields as $field) {
            if (!isset($data[$field])) $data[$field] = $existing[$field];
        }

        if ($this->book->update($id, $data)) {
            Response::success("Book updated successfully");
        } else {
            Response::error("Unable to update book", 503);
        }
    }

    public function delete($id) {
        Auth::validateToken();
        if (!$id) Response::error("ID is required");

        if ($this->book->delete($id)) {
            Response::success("Book deleted successfully");
        } else {
            Response::error("Unable to delete book", 503);
        }
    }
}
?>
