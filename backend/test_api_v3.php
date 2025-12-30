<?php
// Simple API Test Script - run with php test_api_v3.php

$baseUrl = "http://localhost:8000/api/index.php"; 

function makeRequest($method, $endpoint, $data = null, $token = null) {
    global $baseUrl;
    $ch = curl_init();
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

echo "Starting API Tests (User Schema)...\n";

// 1. Login
echo "\n1. Testing Login...\n";
$loginData = ['email' => 'admin@example.com', 'password' => 'password123'];
$loginRes = makeRequest('POST', 'auth&action=login', $loginData);

if ($loginRes['code'] == 200 && isset($loginRes['body']['data']['token'])) {
    $token = $loginRes['body']['data']['token'];
    echo "Login Successful!\n";
} else {
    echo "Login Failed!\n";
    print_r($loginRes);
    exit;
}

// 2. Create Author
echo "\n2. Testing Create Author...\n";
$authorData = [
    'name' => 'J.R.R. Tolkien',
    'country' => 'UK',
    'no_of_books_published' => 15, // Example
    'about' => 'Writer and philologist.',
    'website_url' => 'http://tolkiensociety.org',
    'socialmedia_url' => 'twitter.com/tolkien',
    'image_url' => 'http://example.com/tolkien.jpg'
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
    // New schema uses author_id
    if (count($getRes['body']['data']) > 0) {
        $author = $getRes['body']['data'][count($getRes['body']['data']) - 1]; // Get last created
        // Check for author_id
        if (isset($author['author_id'])) {
            $authorId = $author['author_id'];
            echo "Author Fetched ID: $authorId\n";
        } else {
            echo "Warning: author_id not found in response.\n";
            print_r($author);
        }
    }
} else {
    echo "Get Authors Failed!\n";
    print_r($getRes);
}

// Create Publisher (Needed for Book)
echo "\n4. Testing Create Publisher...\n";
$pubData = ['name' => 'Allen & Unwin', 'country' => 'Australia', 'website_url' => 'allenandunwin.com'];
$pubRes = makeRequest('POST', 'publishers', $pubData, $token);
if ($pubRes['code'] == 201) {
    echo "Publisher Created!\n";
    // Fetch generic list to get ID
    $pubs = makeRequest('GET', 'publishers');
    $publisherId = $pubs['body']['data'][count($pubs['body']['data']) - 1]['publisher_id'];
} else {
    echo "Create Publisher Failed!\n";
    print_r($pubRes);
}

// 5. Create Book
if (isset($authorId) && isset($publisherId)) {
    echo "\n5. Testing Create Book...\n";
    $bookData = [
        'title' => 'The Hobbit',
        'category' => 'Fantasy', 
        'description' => 'A hobbit goes on an adventure.',
        'year_of_publish' => 1937,
        'number_of_chapters' => 19,
        'language' => 'English',
        'image_url' => 'http://example.com/hobbit.jpg',
        'publisher_id' => $publisherId,
        'author_id' => $authorId
    ];
    $bookRes = makeRequest('POST', 'books', $bookData, $token);
    if ($bookRes['code'] == 201) {
        echo "Book Created Successfully!\n";
    } else {
        echo "Create Book Failed!\n";
        print_r($bookRes);
    }
}

echo "\nTests Completed.\n";
?>
