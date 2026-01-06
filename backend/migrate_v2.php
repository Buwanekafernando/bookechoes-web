<?php
require_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Database connection failed. Please ensure MySQL is running and credentials are correct.\n");
}

echo "Starting Database Migration...\n";

// Helper function to add column if not exists
function addColumnIfNotExists($db, $table, $column, $definition) {
    try {
        $check = $db->query("SHOW COLUMNS FROM $table LIKE '$column'");
        if ($check->rowCount() == 0) {
            $sql = "ALTER TABLE $table ADD $column $definition";
            $db->exec($sql);
            echo "Added column '$column' to table '$table'.\n";
        } else {
            echo "Column '$column' already exists in table '$table'.\n";
        }
    } catch (PDOException $e) {
        echo "Error creating column $column in $table: " . $e->getMessage() . "\n";
    }
}

// 1. Add 'status' to all Tables
$tables = ['Author', 'Publisher', 'Book', 'Ebook', 'Bookshops', 'Book_Events'];
foreach ($tables as $table) {
    addColumnIfNotExists($db, $table, 'status', "ENUM('draft', 'published') DEFAULT 'draft'");
}

// 2. Add 'image_url' to Bookshops and Book_Events
addColumnIfNotExists($db, 'Bookshops', 'image_url', "VARCHAR(255)");
addColumnIfNotExists($db, 'Bookshops', 'description', "TEXT"); // Ensure description exists
addColumnIfNotExists($db, 'Book_Events', 'image_url', "VARCHAR(255)");
addColumnIfNotExists($db, 'Book_Events', 'description', "TEXT");

echo "Migration Completed.\n";
?>
