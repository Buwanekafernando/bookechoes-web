<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Publisher.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Auth.php';

class PublisherController extends Controller {
    private $publisher;

    public function __construct($db) {
        parent::__construct($db);
        $this->publisher = new Publisher($db);
    }

    public function getAll() {
        $stmt = $this->publisher->readAll();
        $publishers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Response::success("Publishers fetched successfully", $publishers);
    }

    public function getOne($id) {
        if (!$id) Response::error("ID is required");
        $publisher = $this->publisher->readOne($id);
        if ($publisher) {
            Response::success("Publisher fetched successfully", $publisher);
        } else {
            Response::error("Publisher not found", 404);
        }
    }

    public function create() {
        Auth::validateToken();
        $data = $this->getInput();
        $errors = Validator::validateRequired($data, ['name']); // others optional

        if (!empty($errors)) {
            Response::error("Validation Error", 400, $errors);
        }
        $data = Validator::sanitize($data);
        
        if (!isset($data['country'])) $data['country'] = null;
        if (!isset($data['website_url'])) $data['website_url'] = null;

        if ($this->publisher->create($data)) {
            Response::success("Publisher created successfully", null, 201);
        } else {
            Response::error("Unable to create publisher", 503);
        }
    }

    public function update($id) {
        Auth::validateToken();
        if (!$id) Response::error("ID is required");
        $data = $this->getInput();
        $data = Validator::sanitize($data);

        $existing = $this->publisher->readOne($id);
        if (!$existing) Response::error("Publisher not found", 404);
        
        $fields = ['name', 'country', 'website_url'];
        foreach ($fields as $field) {
            if (!isset($data[$field])) $data[$field] = $existing[$field];
        }

        if ($this->publisher->update($id, $data)) {
            Response::success("Publisher updated successfully");
        } else {
            Response::error("Unable to update publisher", 503);
        }
    }

    public function delete($id) {
        Auth::validateToken();
        if (!$id) Response::error("ID is required");

        if ($this->publisher->delete($id)) {
            Response::success("Publisher deleted successfully");
        } else {
            Response::error("Unable to delete publisher", 503);
        }
    }
}
?>
