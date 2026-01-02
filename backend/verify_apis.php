<?php
// Verification Script for BookEchoes APIs
// Usage: Execute this via CLI or Browser: php backend/verify_apis.php

$baseUrl = "http://localhost/backend/api/index.php";

echo "Starting API Verification...\n";
echo "Base URL: $baseUrl\n\n";

function callAPI($method, $url, $data = false, $token = null) {
    $curl = curl_init();
    
    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case "DELETE":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
            break;
        default:
            if ($data) $url = sprintf("%s?%s", $url, http_build_query($data));
    }
    
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    
    $result = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    return ['code' => $httpCode, 'response' => json_decode($result, true)];
}

// 1. Login (Skipped - Auth Removed for Simple CRUD)
echo "1. Login checks disabled (Simple Config).\n";
$token = null; 

// 2. Author CRUD
echo "\n2. Testing Author CRUD...\n";
// Create
$authorData = ['name' => 'Test Author', 'country' => 'Test Country', 'no_of_books_published' => 10];
$createAuthor = callAPI('POST', "$baseUrl/authors", $authorData); // No Token
echo "   Create: code " . $createAuthor['code'] . "\n";

// Get All (to find ID)
$getAuthors = callAPI('GET', "$baseUrl/authors");
// Debug: print_r($getAuthors['response']);
$authorId = 0;
if (isset($getAuthors['response']['body']) && count($getAuthors['response']['body']) > 0) {
    // Pick the last one which should be ours
    $last = end($getAuthors['response']['body']);
    $authorId = $last['author_id'];
    echo "   Get All: Found Author ID $authorId\n";
} else {
    echo "   Get All: No authors found?\n";
}

// Update
if ($authorId) {
    $updateData = ['name' => 'Updated Author', 'country' => 'New Country'];
    $updateAuthor = callAPI('PUT', "$baseUrl/authors/$authorId", $updateData);
    echo "   Update: code " . $updateAuthor['code'] . "\n";
    
    // Get One
    $getOneAuthor = callAPI('GET', "$baseUrl/authors/$authorId");
    if (isset($getOneAuthor['response']['name']) && $getOneAuthor['response']['name'] === 'Updated Author') {
        echo "   Get One: Verified updated name.\n";
    }
}

// 3. Publisher Helper
// Need a publisher for Book tests
echo "\n3. Creating Helper Publisher...\n";
$pubData = ['name' => 'Test Publisher', 'country' => 'USA'];
callAPI('POST', "$baseUrl/publishers", $pubData);
$getPubs = callAPI('GET', "$baseUrl/publishers");
$pubId = 0;
if (isset($getPubs['response']['body'])) {
    $lastPub = end($getPubs['response']['body']);
    $pubId = $lastPub['publisher_id'];
    echo "   Publisher Created with ID $pubId\n";
}

// 4. Book CRUD
echo "\n4. Testing Book CRUD...\n";
$bookId = 0;
if ($authorId && $pubId) {
    $bookData = ['title' => 'Test Book', 'author_id' => $authorId, 'publisher_id' => $pubId, 'year_of_publish' => 2023];
    $createBook = callAPI('POST', "$baseUrl/books", $bookData);
    echo "   Create: code " . $createBook['code'] . "\n";
    
    $getBooks = callAPI('GET', "$baseUrl/books");
    if (isset($getBooks['response']['body'])) {
        $lastBook = end($getBooks['response']['body']);
        $bookId = $lastBook['book_id'];
        echo "   Book Created with ID $bookId\n";
    }
}

// 5. Bookshop & Inventory
echo "\n5. Testing Bookshop & Inventory...\n";
$shopData = ['name' => 'Test Shop', 'location' => 'City'];
callAPI('POST', "$baseUrl/bookshops", $shopData);
$getShops = callAPI('GET', "$baseUrl/bookshops");
$shopId = 0;
if (isset($getShops['response']['body'])) {
    $lastShop = end($getShops['response']['body']);
    $shopId = $lastShop['bookshop_id'];
    echo "   Shop Created with ID $shopId\n";
}

if ($shopId && $bookId) {
    echo "   Adding Inventory...\n";
    $invData = ['book_id' => $bookId, 'stock_quantity' => 50, 'price' => 19.99];
    // Notice URI handling for inventory
    $addInv = callAPI('POST', "$baseUrl/bookshops/$shopId/inventory", $invData);
    echo "   Add Inventory: code " . $addInv['code'] . "\n"; 
    
    $getInv = callAPI('GET', "$baseUrl/bookshops/$shopId/inventory");
    // print_r($getInv['response']); // Debug
    if (isset($getInv['response']['body'][0]['stock_quantity'])) {
        echo "   Get Inventory: Success. Stock: " . $getInv['response']['body'][0]['stock_quantity'] . "\n";
    }
}

// Cleanup (Optional - Delete created items)
// echo "\nCleanup...\n";
// if ($authorId) callAPI('DELETE', "$baseUrl/authors/$authorId", false, $token);

echo "\nVerification Complete.\n";
?>
