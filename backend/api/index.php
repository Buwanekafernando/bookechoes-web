<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/AuthController.php';
// We will require other controllers here as we implement them
// require_once __DIR__ . '/../controllers/AuthorController.php';

$database = new Database();
$db = $database->getConnection();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// URI Structure: /backend/api/index.php/resource OR /api/resource depending on server config
// We assume access via something like http://localhost/bookechoes/backend/api/resource
// So strict counting is risky. Let's find 'api' and look after it, or just look at last segments.

// Basic Router
// If last segment is 'login', route to AuthController
// If 'authors', route to AuthorController etc.

$resource = null;
$id = null;

// Basic Router
// If last segment is 'login', route to AuthController

// Helper to determine resource and ID
// Examples: /backend/api/index.php/authors, /api/authors/5
$resource = null;
$id = null;

// Filter out empty segments and 'index.php'
$cleanUri = array_values(array_filter($uri, function($v) {
    return $v !== '' && $v !== 'index.php' && $v !== 'api' && $v !== 'backend'; 
}));


$count = count($cleanUri);

// Logic for inventory (nested)
if ($count >= 3 && $cleanUri[$count-2] === 'inventory') {
    $resource = 'bookshops';
} elseif ($count >= 2 && $cleanUri[$count-1] === 'inventory') {
    $resource = 'bookshops';
} else {
    if ($count > 0) {
        if (is_numeric($cleanUri[$count-1])) {
            $id = $cleanUri[$count-1];
            if ($count > 1) {
                $resource = $cleanUri[$count-2];
            }
        } else {
            $resource = $cleanUri[$count-1];
        }
    }
}


if (!$resource && isset($_GET['endpoint'])) {
    $resource = $_GET['endpoint'];
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    }
}

// Global Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// ROUTING

if ($resource === 'login' || $resource === 'auth') {
    $auth = new AuthController($db);
    $auth->login();
    exit();
}

// Author Routes
if ($resource === 'authors') {
    require_once __DIR__ . '/../controllers/AuthorController.php';
    $controller = new AuthorController($db);
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') { if ($id) $controller->getOne($id); else $controller->getAll(); }
    elseif ($method === 'POST') $controller->create();
    elseif ($method === 'PUT') { if ($id) $controller->update($id); else http_response_code(400); }
    elseif ($method === 'DELETE') { if ($id) $controller->delete($id); else http_response_code(400); }
    exit();
}

// Book Routes
if ($resource === 'books') {
    require_once __DIR__ . '/../controllers/BookController.php';
    $controller = new BookController($db);
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') { if ($id) $controller->getOne($id); else $controller->getAll(); }
    elseif ($method === 'POST') $controller->create();
    elseif ($method === 'PUT') { if ($id) $controller->update($id); else http_response_code(400); }
    elseif ($method === 'DELETE') { if ($id) $controller->delete($id); else http_response_code(400); }
    exit();
}

// Ebook Routes
if ($resource === 'ebooks') {
    require_once __DIR__ . '/../controllers/EbookController.php';
    $controller = new EbookController($db);
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') { if ($id) $controller->getOne($id); else $controller->getAll(); }
    elseif ($method === 'POST') $controller->create();
    elseif ($method === 'PUT') { if ($id) $controller->update($id); else http_response_code(400); }
    elseif ($method === 'DELETE') { if ($id) $controller->delete($id); else http_response_code(400); }
    exit();
}
// AI Ingestion Routes
if ($resource === 'ai') {
    require_once __DIR__ . '/../controllers/AIController.php';
    $controller = new AIController($db);
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'ingest') {
        $controller->ingest();
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid action']);
    }
    exit();
}
// Publisher Routes
if ($resource === 'publishers') {
    require_once __DIR__ . '/../controllers/PublisherController.php';
    $controller = new PublisherController($db);
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') { if ($id) $controller->getOne($id); else $controller->getAll(); }
    elseif ($method === 'POST') $controller->create();
    elseif ($method === 'PUT') { if ($id) $controller->update($id); else http_response_code(400); }
    elseif ($method === 'DELETE') { if ($id) $controller->delete($id); else http_response_code(400); }
    exit();
}

// Bookshop Routes
if ($resource === 'bookshops') {
    require_once __DIR__ . '/../controllers/BookshopController.php';
    $controller = new BookshopController($db);
    $method = $_SERVER['REQUEST_METHOD'];

    $isInventory = false;
    $shopId = null;
    $bookId = null;
    
    $invKey = array_search('inventory', $cleanUri);
    if ($invKey !== false) {
        $isInventory = true;
        $shopId = isset($cleanUri[$invKey-1]) ? $cleanUri[$invKey-1] : null;
        $bookId = isset($cleanUri[$invKey+1]) ? $cleanUri[$invKey+1] : null;
    }

    if ($isInventory && $shopId) {
        if ($method === 'GET') {
            $controller->getInventory($shopId);
        } elseif ($method === 'POST') {
            $controller->addInventory($shopId);
        } elseif ($method === 'PUT') {
            if ($bookId) $controller->updateInventory($shopId, $bookId); else http_response_code(400);
        } elseif ($method === 'DELETE') {
            if ($bookId) $controller->removeInventory($shopId, $bookId); else http_response_code(400);
        }
    } else {
        if ($method === 'GET') { if ($id) $controller->getOne($id); else $controller->getAll(); }
        elseif ($method === 'POST') $controller->create();
        elseif ($method === 'PUT') { if ($id) $controller->update($id); else http_response_code(400); }
        elseif ($method === 'DELETE') { if ($id) $controller->delete($id); else http_response_code(400); }
    }
    exit();
}


http_response_code(404);
echo json_encode(array("message" => "Endpoint not found."));
?>
