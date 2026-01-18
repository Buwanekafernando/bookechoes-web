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
        $itemCount = count($events);
        
        if ($itemCount > 0) {
            $arr = array("body" => $events, "itemCount" => $itemCount);
            echo json_encode($arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No record found."));
        }
    }

    public function getOne($id) {
        if (!$id) {
            http_response_code(400);
            echo json_encode(array("message" => "ID is required."));
            return;
        }
        $data = $this->event->readOne($id);
        if ($data) {
            http_response_code(200);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Event not found."));
        }
    }

    public function create() {
        Auth::validateToken();
        $data = $this->getInput();
        if (empty($data['name']) || empty($data['location']) || empty($data['date_start'])) {
            http_response_code(400);
            echo json_encode(array("message" => "Validation Error: name, location and date_start are required."));
            return;
        }

        if (!isset($data['date_end'])) $data['date_end'] = null;

        if ($this->event->create($data)) {
            http_response_code(201);
            echo json_encode(array("message" => "Event created successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Unable to create event."));
        }
    }

    public function update($id) {
        Auth::validateToken();
        if (!$id) {
            http_response_code(400);
            echo json_encode(array("message" => "ID is required."));
            return;
        }
        $data = $this->getInput();
        
        $existing = $this->event->readOne($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(array("message" => "Event not found."));
            return;
        }
        
        $fields = ['name', 'location', 'date_start', 'date_end'];
        foreach ($fields as $field) {
            if (!isset($data[$field])) $data[$field] = $existing[$field];
        }

        if ($this->event->update($id, $data)) {
            http_response_code(200);
            echo json_encode(array("message" => "Event updated successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Unable to update event."));
        }
    }

    public function delete($id) {
        Auth::validateToken();
        if (!$id) {
            http_response_code(400);
            echo json_encode(array("message" => "ID is required."));
            return;
        }

        if ($this->event->delete($id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Event deleted."));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Unable to delete event."));
        }
    }
}
?>
