<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Author.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Auth.php';

class AuthorController extends Controller {
    private $author;

    public function __construct($db) {
        parent::__construct($db);
        $this->author = new Author($db);
    }

    public function getAll() {
        $stmt = $this->author->readAll();
        $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Response::success("Authors fetched successfully", $authors);
    }

    public function getOne($id) {
        if (!$id) Response::error("ID is required");
        
        $author = $this->author->readOne($id);
        if ($author) {
            Response::success("Author fetched successfully", $author);
        } else {
            Response::error("Author not found", 404);
        }
    }

    public function create() {
        Auth::validateToken(); // Protected endpoint
        
        $data = $this->getInput();
        $errors = Validator::validateRequired($data, ['name']); 

        if (!empty($errors)) {
            Response::error("Validation Error", 400, $errors);
        }

        // Sanitize
        $data = Validator::sanitize($data);

        // Fill optional fields with null/defaults if missing to avoid index errors, or let DB handle it.
        // Best practice: ensure array keys exist
        $fields = ['country', 'no_of_books_published', 'about', 'website_url', 'socialmedia_url', 'image_url'];
        foreach ($fields as $field) {
            if (!isset($data[$field])) $data[$field] = null;
        }

        if ($this->author->create($data)) {
            Response::success("Author created successfully", null, 201);
        } else {
            Response::error("Unable to create author", 503);
        }
    }

    public function update($id) {
        Auth::validateToken(); // Protected endpoint
        
        if (!$id) Response::error("ID is required");
        
        $data = $this->getInput();
        $data = Validator::sanitize($data);
        
        $fields = ['name', 'country', 'no_of_books_published', 'about', 'website_url', 'socialmedia_url', 'image_url'];
        // For update, we might want to fetch existing first or just update what's passed?
        // Simpler for now: Expect all fields or merge with existing. 
        // Let's assume the frontend sends the full object. If not, we should merge.
        // For this task, I'll just fill defaults if missing, which might overwrite with empty.
        // Ideally: Fetch -> Merge -> Update.
        
        $existing = $this->author->readOne($id);
        if (!$existing) Response::error("Author not found", 404);
        
        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                $data[$field] = $existing[$field];
            }
        }

        if ($this->author->update($id, $data)) {
            Response::success("Author updated successfully");
        } else {
            Response::error("Unable to update author", 503);
        }
    }

    public function delete($id) {
        Auth::validateToken(); // Protected endpoint
        if (!$id) Response::error("ID is required");

        if ($this->author->delete($id)) {
            Response::success("Author deleted successfully");
        } else {
            Response::error("Unable to delete author", 503);
        }
    }
}
?>
