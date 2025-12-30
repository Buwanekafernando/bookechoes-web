<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/Database.php';
include_once '../utils/Response.php';

// Instantiate DB
$database = new Database();
$db = $database->getConnection();

// Parse Request
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriSegments = explode('/', trim($uri, '/'));

// Adjust this based on your folder structure if needed. 
// Assuming localhost/backend/api/index.php results in segments: [backend, api, entity]
// If hosted at root, it might be different. 
// Let's assume the last segment is the ID if numeric, and the one before is the entity.
// OR we use query params ?resource=authors
// Let's stick to a simple query param style for now: api/index.php?endpoint=authors
// It is easier to configure without .htaccess on standard PHP setups.

$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : null;
// Support clean URLs if rewrites are enabled, but fallback to query param
if (!$endpoint && isset($uriSegments[2])) {
    // This part is tricky without knowing the exact URL structure user will run. 
    // I will check if $_GET['endpoint'] is passed via RewriteRule or direct param.
}

if (!$endpoint) {
    Response::error("No endpoint specified", 404);
}

// Router Logic
switch ($endpoint) {
    case 'auth':
        include_once '../controllers/AuthController.php';
        $controller = new AuthController($db);
        $action = isset($_GET['action']) ? $_GET['action'] : null;
        if ($action == 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login();
        } elseif ($action == 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->register();
        } else {
            Response::error("Invalid action or method", 405);
        }
        break;

    case 'authors':
        include_once '../controllers/AuthorController.php';
        $controller = new AuthorController($db);
        processRequest($controller);
        break;

    case 'publishers':
        include_once '../controllers/PublisherController.php';
        $controller = new PublisherController($db);
        processRequest($controller);
        break;

    case 'books':
        include_once '../controllers/BookController.php';
        $controller = new BookController($db);
        processRequest($controller);
        break;

    case 'ebooks':
        include_once '../controllers/EbookController.php';
        $controller = new EbookController($db);
        processRequest($controller);
        break;

    case 'bookshops':
        include_once '../controllers/BookshopController.php';
        $controller = new BookshopController($db);
        
        // Custom routing for inventory actions
        $action = isset($_GET['action']) ? $_GET['action'] : null;
        if ($action == 'inventory' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            $controller->getInventory($id);
        } elseif ($action == 'add_inventory' && $_SERVER['REQUEST_METHOD'] === 'POST') {
             $id = isset($_GET['id']) ? $_GET['id'] : null;
             $controller->addInventory($id);
        } elseif ($action == 'update_inventory' && $_SERVER['REQUEST_METHOD'] === 'POST') { // PUT usually but simple POST action here
             $controller->updateInventory();
        } elseif ($action == 'remove_inventory' && $_SERVER['REQUEST_METHOD'] === 'DELETE') {
             $controller->removeInventory();
        } else {
            processRequest($controller);
        }
        break;

    case 'events':
        include_once '../controllers/EventController.php';
        $controller = new EventController($db);
        processRequest($controller);
        break;
        
    // Add other cases here...

    default:
        Response::error("Endpoint not found", 404);
        break;
}

function processRequest($controller) {
    $method = $_SERVER['REQUEST_METHOD'];
    $id = isset($_GET['id']) ? $_GET['id'] : null;

    switch ($method) {
        case 'GET':
            if ($id) {
                $controller->getOne($id);
            } else {
                $controller->getAll();
            }
            break;
        case 'POST':
            $controller->create();
            break;
        case 'PUT':
            $controller->update($id);
            break;
        case 'DELETE':
            $controller->delete($id);
            break;
        default:
            Response::error("Method not allowed", 405);
            break;
    }
}
?>
