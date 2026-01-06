<?php
// Test script for AI ingestion
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/AIController.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Database connection failed.\n");
}

echo "Testing AI Ingestion...\n";

// Step 1: Login as admin to get token
$auth = new AuthController($db);

// Simulate JSON input for login
$loginData = json_encode(['email' => 'admin@example.com', 'password' => 'password123']);
file_put_contents('php://input', $loginData);

ob_start();
$auth->login();
$output = ob_get_clean();
$loginResponse = json_decode($output, true);

if (!isset($loginResponse['token'])) {
    die("Login failed: " . $output . "\n");
}

$token = $loginResponse['token'];
echo "Logged in successfully. Token: " . substr($token, 0, 20) . "...\n";

// Step 2: Test ingestion
$ai = new AIController($db);

// Simulate GET params
$_GET['type'] = 'books';
$_GET['topic'] = 'latest fiction';

// Simulate Authorization header
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

echo "Testing book ingestion...\n";
ob_start();
$ai->ingest();
$ingestOutput = ob_get_clean();
echo "Ingestion result: " . $ingestOutput . "\n";

echo "Test completed.\n";
?>