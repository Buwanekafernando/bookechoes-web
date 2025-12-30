<?php
// Simple API Test Script - run with php test_api_v2.php

$baseUrl = "http://localhost:8000/api/index.php"; // Assuming you run `php -S localhost:8000` in backend/

// Helper to make requests
function makeRequest($method, $endpoint, $data = null, $token = null) {
    global $baseUrl;
    $url = $baseUrl . "?endpoint=" . $endpoint;
    
    // For specific resource actions like GET /authors?id=1 or PUT /authors?id=1
    // We need to parse endpoint which supports params if we passed them in $endpoint string
    // But for this simple helper, distinct params handling is better
    
    // Quick Fix: simple concatenation if ? exists
    // Actually the router uses ?endpoint=authors&id=1 if strictly following the router logic.
    // Router logic: api/index.php?endpoint=authors&id=...
    // My router logic was: $endpoint = $_GET['endpoint']
    // So url construction should be careful.
    
    $ch = curl_init();
    
    // If endpoint has ? already (e.g. authors&id=1), append correctly
    // But it's easier to just pass full query string in $endpoint arg for this test
    
    curl_setopt($ch, CURLOPT_URL, $baseUrl . "?endpoint=" . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
    }
    
    if ($token) {
        $headers = ['Content-Type: application/json', 'Authorization: Bearer ' . $token];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'body' => json_decode($response, true)];
}

echo "Starting API Tests...\n";

// 1. Login
echo "\n1. Testing Login...\n";
$loginData = ['email' => 'admin@example.com', 'password' => 'password123'];
$loginRes = makeRequest('POST', 'auth&action=login', $loginData);

if ($loginRes['code'] == 200 && isset($loginRes['body']['data']['token'])) {
    $token = $loginRes['body']['data']['token'];
    echo "Login Successful! Token: " . substr($token, 0, 10) . "...\n";
} else {
    echo "Login Failed!\n";
    print_r($loginRes);
    exit;
}

// 2. Create Author
echo "\n2. Testing Create Author...\n";
$authorData = [
    'name' => 'J.K. Rowling',
    'bio' => 'British author, philanthropist, and screenwriter.',
    'email' => 'jk@example.com'
];
$createRes = makeRequest('POST', 'authors', $authorData, $token);
if ($createRes['code'] == 201) {
    echo "Author Created Successfully!\n";
} else {
    echo "Create Author Failed!\n";
    print_r($createRes);
}

// 3. Get Authors
echo "\n3. Testing Get Authors...\n";
$getRes = makeRequest('GET', 'authors');
if ($getRes['code'] == 200) {
    echo "Authors Fetched: " . count($getRes['body']['data']) . "\n";
    $authorId = $getRes['body']['data'][0]['id'];
} else {
    echo "Get Authors Failed!\n";
    print_r($getRes);
    exit;
}

// 4. Update Author
if (isset($authorId)) {
    echo "\n4. Testing Update Author (ID: $authorId)...\n";
    $updateData = ['name' => 'J.K. Rowling (Updated)', 'bio' => 'Updated Bio', 'email' => 'jk@updated.com'];
    // Construct endpoint with ID
    $updateRes = makeRequest('PUT', 'authors&id=' . $authorId, $updateData, $token);
    if ($updateRes['code'] == 200) {
        echo "Author Updated Successfully!\n";
    } else {
        echo "Update Author Failed!\n";
        print_r($updateRes);
    }
}

// 5. Delete Author
if (isset($authorId)) {
    echo "\n5. Testing Delete Author (ID: $authorId)...\n";
    $deleteRes = makeRequest('DELETE', 'authors&id=' . $authorId, null, $token);
    if ($deleteRes['code'] == 200) {
        echo "Author Deleted Successfully!\n";
    } else {
        echo "Delete Author Failed!\n";
        print_r($deleteRes);
    }
}

echo "\nTests Completed.\n";
?>
