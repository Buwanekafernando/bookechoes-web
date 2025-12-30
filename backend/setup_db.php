<?php
require_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Users Table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        api_token VARCHAR(255) UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($sql);
    echo "Users table checked/created.\n";

    // Authors Table
    $sql = "CREATE TABLE IF NOT EXISTS authors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        bio TEXT,
        email VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($sql);
    echo "Authors table checked/created.\n";

    // Publishers Table
    $sql = "CREATE TABLE IF NOT EXISTS publishers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        address TEXT,
        contact_email VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($sql);
    echo "Publishers table checked/created.\n";
    
    // Books Table
    $sql = "CREATE TABLE IF NOT EXISTS books (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        isbn VARCHAR(50) UNIQUE,
        published_date DATE,
        author_id INT,
        publisher_id INT,
        price DECIMAL(10,2),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE SET NULL,
        FOREIGN KEY (publisher_id) REFERENCES publishers(id) ON DELETE SET NULL
    )";
    $db->exec($sql);
    echo "Books table checked/created.\n";

    // Ebooks Table
    $sql = "CREATE TABLE IF NOT EXISTS ebooks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        book_id INT,
        file_format VARCHAR(50), 
        file_size VARCHAR(50),
        download_link VARCHAR(255),
        contact_email VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
    )";
    $db->exec($sql);
    echo "Ebooks table checked/created.\n";

    // Bookshops Table
    $sql = "CREATE TABLE IF NOT EXISTS bookshops (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        location TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($sql);
    echo "Bookshops table checked/created.\n";
    
    // Bookshop Books (Inventory)
    $sql = "CREATE TABLE IF NOT EXISTS bookshop_books (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bookshop_id INT,
        book_id INT,
        stock_quantity INT DEFAULT 0,
        FOREIGN KEY (bookshop_id) REFERENCES bookshops(id) ON DELETE CASCADE,
        FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
    )";
    $db->exec($sql);
    echo "Bookshop Inventory table checked/created.\n";

    // Book Events Table
    $sql = "CREATE TABLE IF NOT EXISTS book_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        event_date DATETIME,
        location VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($sql);
    echo "BookEvents table checked/created.\n";
    
    // Insert a default admin user if not exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $email = 'admin@example.com';
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() == 0) {
        $pass = password_hash('password123', PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $email, $pass]);
        echo "Default admin user created (admin@example.com / password123).\n";
    }

} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>
