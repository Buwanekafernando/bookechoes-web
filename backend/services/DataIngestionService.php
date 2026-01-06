<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/GeminiService.php';
require_once __DIR__ . '/../models/Author.php';
require_once __DIR__ . '/../models/Publisher.php';
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../models/Ebook.php';
require_once __DIR__ . '/../models/Bookshop.php';
require_once __DIR__ . '/../models/BookEvent.php';

class DataIngestionService {
    private $db;
    private $gemini;
    private $logFile = __DIR__ . '/../logs/ingestion.log';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->gemini = new GeminiService();
        if (!file_exists(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
    }

    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($this->logFile, "[$timestamp] $message\n", FILE_APPEND);
    }

    private function validateImageUrl($url) {
        if (empty($url)) return '';
        // Basic validation: check if it's a valid URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        return '';
    }

    private function findOrCreateAuthor($authorData) {
        $authorModel = new Author($this->db);
        // Check if author exists by name
        $query = "SELECT author_id FROM Author WHERE name = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $authorData['name']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result['author_id'];
        }
        // Create new author
        $data = [
            'name' => $authorData['name'],
            'country' => $authorData['country'] ?? '',
            'no_of_books_published' => $authorData['no_of_books_published'] ?? 0,
            'about' => $authorData['about'] ?? '',
            'website_url' => $authorData['website_url'] ?? '',
            'socialmedia_url' => $authorData['socialmedia_url'] ?? '',
            'image_url' => $this->validateImageUrl($authorData['image_url'] ?? ''),
            'status' => 'draft'
        ];
        if ($authorModel->create($data)) {
            $this->log("Created new author: " . $authorData['name']);
            return $this->db->lastInsertId();
        }
        return null;
    }

    private function findOrCreatePublisher($publisherData) {
        $publisherModel = new Publisher($this->db);
        // Check if publisher exists by name
        $query = "SELECT publisher_id FROM Publisher WHERE name = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $publisherData['name']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result['publisher_id'];
        }
        // Create new publisher
        $data = [
            'name' => $publisherData['name'],
            'country' => $publisherData['country'] ?? '',
            'website_url' => $publisherData['website_url'] ?? '',
            'image_url' => $this->validateImageUrl($publisherData['image_url'] ?? ''),
            'status' => 'draft'
        ];
        if ($publisherModel->create($data)) {
            $this->log("Created new publisher: " . $publisherData['name']);
            return $this->db->lastInsertId();
        }
        return null;
    }

    public function ingestBooks($topic = "latest fiction") {
        $this->log("Starting book ingestion for topic: $topic");
        $data = $this->gemini->fetchBooks($topic);
        if (isset($data['error'])) {
            $this->log("Error fetching books: " . $data['error']);
            return ['error' => $data['error']];
        }
        $bookModel = new Book($this->db);
        $ingested = 0;
        foreach ($data as $bookData) {
            // Check if book exists by title and author name
            $authorId = $this->findOrCreateAuthor($bookData['author']);
            $publisherId = $this->findOrCreatePublisher($bookData['publisher']);
            if (!$authorId || !$publisherId) {
                $this->log("Failed to create/find author or publisher for book: " . $bookData['title']);
                continue;
            }
            $query = "SELECT book_id FROM Book WHERE title = ? AND author_id = ? LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $bookData['title']);
            $stmt->bindParam(2, $authorId);
            $stmt->execute();
            if ($stmt->fetch()) {
                $this->log("Book already exists: " . $bookData['title']);
                continue;
            }
            // Create book
            $bookInsert = [
                'title' => $bookData['title'],
                'category' => $bookData['category'],
                'description' => $bookData['description'],
                'year_of_publish' => $bookData['year_of_publish'],
                'number_of_chapters' => $bookData['number_of_chapters'],
                'language' => $bookData['language'],
                'image_url' => $this->validateImageUrl($bookData['image_url']),
                'author_id' => $authorId,
                'publisher_id' => $publisherId,
                'status' => 'draft'
            ];
            if ($bookModel->create($bookInsert)) {
                $this->log("Ingested book: " . $bookData['title']);
                $ingested++;
            } else {
                $this->log("Failed to ingest book: " . $bookData['title']);
            }
        }
        $this->log("Book ingestion completed. Ingested: $ingested");
        return ['ingested' => $ingested];
    }

    public function ingestAuthors($genre = "famous") {
        $this->log("Starting author ingestion for genre: $genre");
        $data = $this->gemini->fetchAuthors($genre);
        if (isset($data['error'])) {
            $this->log("Error fetching authors: " . $data['error']);
            return ['error' => $data['error']];
        }
        $authorModel = new Author($this->db);
        $ingested = 0;
        foreach ($data as $authorData) {
            if ($this->findOrCreateAuthor($authorData)) {
                $ingested++;
            }
        }
        $this->log("Author ingestion completed. Ingested: $ingested");
        return ['ingested' => $ingested];
    }

    public function ingestBookshops($location = "global") {
        $this->log("Starting bookshop ingestion for location: $location");
        $data = $this->gemini->fetchBookshops($location);
        if (isset($data['error'])) {
            $this->log("Error fetching bookshops: " . $data['error']);
            return ['error' => $data['error']];
        }
        $bookshopModel = new Bookshop($this->db);
        $ingested = 0;
        foreach ($data as $shopData) {
            // Check if exists by name
            $query = "SELECT bookshop_id FROM Bookshops WHERE name = ? LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $shopData['name']);
            $stmt->execute();
            if ($stmt->fetch()) {
                $this->log("Bookshop already exists: " . $shopData['name']);
                continue;
            }
            $insert = [
                'name' => $shopData['name'],
                'location' => $shopData['location'],
                'country' => $shopData['country'],
                'description' => $shopData['description'],
                'image_url' => $this->validateImageUrl($shopData['image_url']),
                'status' => 'draft'
            ];
            if ($bookshopModel->create($insert)) {
                $this->log("Ingested bookshop: " . $shopData['name']);
                $ingested++;
            }
        }
        $this->log("Bookshop ingestion completed. Ingested: $ingested");
        return ['ingested' => $ingested];
    }

    public function ingestEvents() {
        $this->log("Starting event ingestion");
        $data = $this->gemini->fetchEvents();
        if (isset($data['error'])) {
            $this->log("Error fetching events: " . $data['error']);
            return ['error' => $data['error']];
        }
        $eventModel = new BookEvent($this->db);
        $ingested = 0;
        foreach ($data as $eventData) {
            // Check if exists by name
            $query = "SELECT book_event_id FROM Book_Events WHERE name = ? LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $eventData['name']);
            $stmt->execute();
            if ($stmt->fetch()) {
                $this->log("Event already exists: " . $eventData['name']);
                continue;
            }
            $insert = [
                'name' => $eventData['name'],
                'location' => $eventData['location'],
                'description' => $eventData['description'],
                'date_start' => $eventData['date_start'],
                'date_end' => $eventData['date_end'],
                'image_url' => $this->validateImageUrl($eventData['image_url']),
                'status' => 'draft'
            ];
            if ($eventModel->create($insert)) {
                $this->log("Ingested event: " . $eventData['name']);
                $ingested++;
            }
        }
        $this->log("Event ingestion completed. Ingested: $ingested");
        return ['ingested' => $ingested];
    }

    public function ingestNews() {
        $this->log("Starting news ingestion");
        $data = $this->gemini->fetchNews();
        if (isset($data['error'])) {
            $this->log("Error fetching news: " . $data['error']);
            return ['error' => $data['error']];
        }
        // For news, since there's no model, perhaps store in a news table or just log for now.
        // The user mentioned "related news", but no table for news. Maybe skip or create a simple table.
        // For now, just log the news items.
        foreach ($data as $newsData) {
            $this->log("News: " . $newsData['title'] . " - " . $newsData['url']);
        }
        $this->log("News ingestion completed.");
        return ['ingested' => count($data)];
    }

    public function ingestAll() {
        $results = [];
        $results['books'] = $this->ingestBooks();
        $results['authors'] = $this->ingestAuthors();
        $results['bookshops'] = $this->ingestBookshops();
        $results['events'] = $this->ingestEvents();
        $results['news'] = $this->ingestNews();
        return $results;
    }
}
?>