<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/BookEvent.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/Auth.php';

class EventController extends Controller {
    private $event;

    public function __construct($db) {
        parent::__construct($db);
        $this->event = new BookEvent($db);
    }

    public function getAll() {
        $stmt = $this->event->readAll();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Response::success("Events fetched successfully", $events);
    }

    public function getOne($id) {
        if (!$id) Response::error("ID is required");
        $event = $this->event->readOne($id);
        if ($event) {
            Response::success("Event fetched successfully", $event);
        } else {
            Response::error("Event not found", 404);
        }
    }

    public function create() {
        Auth::validateToken();
        $data = $this->getInput();
        $errors = Validator::validateRequired($data, ['name', 'location', 'date_start']);

        if (!empty($errors)) {
            Response::error("Validation Error", 400, $errors);
        }
        $data = Validator::sanitize($data);
        
        if (!isset($data['date_end'])) $data['date_end'] = null;

        if ($this->event->create($data)) {
            Response::success("Event created successfully", null, 201);
        } else {
            Response::error("Unable to create event", 503);
        }
    }

    public function update($id) {
        Auth::validateToken();
        if (!$id) Response::error("ID is required");
        $data = $this->getInput();
        $data = Validator::sanitize($data);
        
        $existing = $this->event->readOne($id);
        if (!$existing) Response::error("Event not found", 404);
        
        $fields = ['name', 'location', 'date_start', 'date_end'];
        foreach ($fields as $field) {
            if (!isset($data[$field])) $data[$field] = $existing[$field];
        }

        if ($this->event->update($id, $data)) {
            Response::success("Event updated successfully");
        } else {
            Response::error("Unable to update event", 503);
        }
    }

    public function delete($id) {
        Auth::validateToken();
        if (!$id) Response::error("ID is required");

        if ($this->event->delete($id)) {
            Response::success("Event deleted successfully");
        } else {
            Response::error("Unable to delete event", 503);
        }
    }
}
?>
