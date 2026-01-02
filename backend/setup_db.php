<?php
require_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Users Table (Retaining for Admin Auth)
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
    // Model: Author (author_id, name, country, no_of_books_published, about, website_url, socialmedia_url, image_url)
    $sql = "CREATE TABLE IF NOT EXISTS Author (
        author_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        country VARCHAR(255),
        no_of_books_published INT DEFAULT 0,
        about TEXT,
        website_url VARCHAR(255),
        socialmedia_url VARCHAR(255),
        image_url VARCHAR(255)
    )";
    $db->exec($sql);
    echo "Author table checked/created.\n";

    // Publishers Table
    // Model: Publisher (publisher_id, name, country, website_url)
    $sql = "CREATE TABLE IF NOT EXISTS Publisher (
        publisher_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        country VARCHAR(255),
        website_url VARCHAR(255)
    )";
    $db->exec($sql);
    echo "Publisher table checked/created.\n";

    // Books Table
    // Model: Book (book_id, title, category, description, year_of_publish, number_of_chapters, language, image_url, publisher_id, author_id)
    $sql = "CREATE TABLE IF NOT EXISTS Book (
        book_id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(255),
        description TEXT,
        year_of_publish INT,
        number_of_chapters INT,
        language VARCHAR(50),
        image_url VARCHAR(255),
        publisher_id INT,
        author_id INT,
        FOREIGN KEY (publisher_id) REFERENCES Publisher(publisher_id) ON DELETE SET NULL,
        FOREIGN KEY (author_id) REFERENCES Author(author_id) ON DELETE SET NULL
    )";
    $db->exec($sql);
    echo "Book table checked/created.\n";

    // Ebooks Table
    // Model: Ebook (ebook_id, name, category, description, year_of_publish, language, number_of_chapters, image_url, author_id)
    $sql = "CREATE TABLE IF NOT EXISTS Ebook (
        ebook_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category VARCHAR(255),
        description TEXT,
        year_of_publish INT,
        language VARCHAR(50),
        number_of_chapters INT,
        image_url VARCHAR(255),
        author_id INT,
        FOREIGN KEY (author_id) REFERENCES Author(author_id) ON DELETE SET NULL
    )";
    $db->exec($sql);
    echo "Ebook table checked/created.\n";

    // Bookshops Table
    // Model: Bookshops (bookshop_id, name, location, country)
    $sql = "CREATE TABLE IF NOT EXISTS Bookshops (
        bookshop_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        location TEXT,
        country VARCHAR(255)
    )";
    $db->exec($sql);
    echo "Bookshops table checked/created.\n";

    // Bookshop Books (Inventory)
    // Model: Bookshop_Books (bookshop_id, book_id, stock_quantity, price)
    $sql = "CREATE TABLE IF NOT EXISTS Bookshop_Books (
        bookshop_id INT,
        book_id INT,
        stock_quantity INT DEFAULT 0,
        price DECIMAL(10, 2),
        PRIMARY KEY (bookshop_id, book_id),
        FOREIGN KEY (bookshop_id) REFERENCES Bookshops(bookshop_id) ON DELETE CASCADE,
        FOREIGN KEY (book_id) REFERENCES Book(book_id) ON DELETE CASCADE
    )";
    $db->exec($sql);
    echo "Bookshop_Books table checked/created.\n";

    // Book Events Table
    // Model: Book_Events (book_event_id, name, location, date_start, date_end)
    $sql = "CREATE TABLE IF NOT EXISTS Book_Events (
        book_event_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        location VARCHAR(255),
        date_start DATETIME,
        date_end DATETIME
    )";
    $db->exec($sql);
    echo "Book_Events table checked/created.\n";

    // Insert a default admin user if not exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $email = 'admin@example.com';
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() == 0) {
        $pass = password_hash('password123', PASSWORD_BCRYPT);
        // Token will be generated upon login, but we can set a manual one for testing if needed.
        $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $email, $pass]);
        echo "Default admin user created (admin@example.com / password123).\n";
    }

} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>
