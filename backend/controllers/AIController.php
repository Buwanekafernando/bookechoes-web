<?php
require_once __DIR__ . '/../services/GeminiService.php';
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../models/Author.php';
require_once __DIR__ . '/../models/Publisher.php';
require_once __DIR__ . '/../models/Bookshop.php';
require_once __DIR__ . '/../models/BookEvent.php';
require_once __DIR__ . '/../models/Ebook.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AIController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function ingest() {
        // Authenticate user
        $auth = new AuthMiddleware($this->conn);
        $user = $auth->authenticate();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            return;
        }

        // Get type from query params
        $type = isset($_GET['type']) ? $_GET['type'] : null;
        $topic = isset($_GET['topic']) ? $_GET['topic'] : null;

        if (!$type) {
            http_response_code(400);
            echo json_encode(['message' => 'Type parameter required (books, authors, bookshops, events, ebooks, news)']);
            return;
        }

        // Determine which API key to use
        $serviceType = ($type === 'events' || $type === 'news') ? 'events' : 'general';
        $gemini = new GeminiService($serviceType);

        $result = [];
        $inserted = 0;
        $errors = [];

        try {
            switch ($type) {
                case 'books':
                    $data = $gemini->fetchBooks($topic ?? 'latest fiction');
                    if (isset($data['error'])) {
                        throw new Exception($data['error']);
                    }
                    $result = $this->ingestBooks($data);
                    break;

                case 'authors':
                    $data = $gemini->fetchAuthors($topic ?? 'famous');
                    if (isset($data['error'])) {
                        throw new Exception($data['error']);
                    }
                    $result = $this->ingestAuthors($data);
                    break;

                case 'bookshops':
                    $data = $gemini->fetchBookshops($topic ?? 'global');
                    if (isset($data['error'])) {
                        throw new Exception($data['error']);
                    }
                    $result = $this->ingestBookshops($data);
                    break;

                case 'events':
                    $data = $gemini->fetchEvents();
                    if (isset($data['error'])) {
                        throw new Exception($data['error']);
                    }
                    $result = $this->ingestEvents($data);
                    break;

                case 'ebooks':
                    $data = $gemini->fetchBooks($topic ?? 'latest ebooks'); // Reuse fetchBooks for ebooks
                    if (isset($data['error'])) {
                        throw new Exception($data['error']);
                    }
                    $result = $this->ingestEbooks($data);
                    break;

                case 'news':
                    $data = $gemini->fetchNews();
                    if (isset($data['error'])) {
                        throw new Exception($data['error']);
                    }
                    $result = $this->ingestNews($data);
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['message' => 'Invalid type']);
                    return;
            }

            http_response_code(200);
            echo json_encode([
                'message' => 'Ingestion completed',
                'type' => $type,
                'inserted' => $result['inserted'],
                'errors' => $result['errors']
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Ingestion failed: ' . $e->getMessage()]);
        }
    }

    private function ingestBooks($books) {
        $inserted = 0;
        $errors = [];
        
        foreach ($books as $bookData) {
            try {
                // First, ensure author exists
                $authorModel = new Author($this->conn);
                $authorId = $this->findOrCreateAuthor($bookData['author'] ?? []);

                // Ensure publisher exists
                $publisherId = $this->findOrCreatePublisher($bookData['publisher'] ?? []);

                // Create book
                $bookModel = new Book($this->conn);
                $data = [
                    'title' => $bookData['title'] ?? 'Untitled',
                    'category' => $bookData['category'] ?? 'General',
                    'description' => $bookData['description'] ?? '',
                    'year_of_publish' => $bookData['year_of_publish'] ?? date('Y'),
                    'number_of_chapters' => $bookData['number_of_chapters'] ?? 0,
                    'language' => $bookData['language'] ?? 'English',
                    'image_url' => $bookData['image_url'] ?? '',
                    'publisher_id' => $publisherId,
                    'author_id' => $authorId,
                    'status' => 'draft'
                ];

                if ($bookModel->create($data)) {
                    $inserted++;
                } else {
                    $errors[] = 'Failed to insert book: ' . ($bookData['title'] ?? 'Unknown');
                }
            } catch (Exception $e) {
                $errors[] = 'Error processing book: ' . $e->getMessage();
            }
        }

        return ['inserted' => $inserted, 'errors' => $errors];
    }

    private function ingestAuthors($authors) {
        $inserted = 0;
        $errors = [];

        foreach ($authors as $authorData) {
            try {
                // Check if author exists
                $query = "SELECT author_id FROM Author WHERE name = ? LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$authorData['name'] ?? '']);
                if ($stmt->fetch()) {
                    continue; // Skip if exists
                }

                $authorModel = new Author($this->conn);
                $data = [
                    'name' => $authorData['name'] ?? 'Unknown',
                    'country' => $authorData['country'] ?? '',
                    'no_of_books_published' => $authorData['no_of_books_published'] ?? 0,
                    'about' => $authorData['about'] ?? '',
                    'website_url' => $authorData['website_url'] ?? '',
                    'socialmedia_url' => $authorData['socialmedia_url'] ?? '',
                    'image_url' => $authorData['image_url'] ?? '',
                    'status' => 'draft'
                ];

                if ($authorModel->create($data)) {
                    $inserted++;
                } else {
                    $errors[] = 'Failed to insert author: ' . ($authorData['name'] ?? 'Unknown');
                }
            } catch (Exception $e) {
                $errors[] = 'Error processing author: ' . $e->getMessage();
            }
        }

        return ['inserted' => $inserted, 'errors' => $errors];
    }

    private function ingestBookshops($bookshops) {
        $inserted = 0;
        $errors = [];

        foreach ($bookshops as $shopData) {
            try {
                // Check if bookshop exists
                $query = "SELECT bookshop_id FROM Bookshops WHERE name = ? LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$shopData['name'] ?? '']);
                if ($stmt->fetch()) {
                    continue; // Skip if exists
                }

                $shopModel = new Bookshop($this->conn);
                $data = [
                    'name' => $shopData['name'] ?? 'Unknown',
                    'location' => $shopData['location'] ?? '',
                    'country' => $shopData['country'] ?? '',
                    'description' => $shopData['description'] ?? '',
                    'image_url' => $shopData['image_url'] ?? '',
                    'status' => 'draft'
                ];

                if ($shopModel->create($data)) {
                    $inserted++;
                } else {
                    $errors[] = 'Failed to insert bookshop: ' . ($shopData['name'] ?? 'Unknown');
                }
            } catch (Exception $e) {
                $errors[] = 'Error processing bookshop: ' . $e->getMessage();
            }
        }

        return ['inserted' => $inserted, 'errors' => $errors];
    }

    private function ingestEvents($events) {
        $inserted = 0;
        $errors = [];

        foreach ($events as $eventData) {
            try {
                // Check if event exists
                $query = "SELECT book_event_id FROM Book_Events WHERE name = ? LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$eventData['name'] ?? '']);
                if ($stmt->fetch()) {
                    continue; // Skip if exists
                }

                $eventModel = new BookEvent($this->conn);
                $data = [
                    'name' => $eventData['name'] ?? 'Unknown Event',
                    'location' => $eventData['location'] ?? '',
                    'description' => $eventData['description'] ?? '',
                    'date_start' => $eventData['date_start'] ?? null,
                    'date_end' => $eventData['date_end'] ?? null,
                    'image_url' => $eventData['image_url'] ?? '',
                    'status' => 'draft'
                ];

                if ($eventModel->create($data)) {
                    $inserted++;
                } else {
                    $errors[] = 'Failed to insert event: ' . ($eventData['name'] ?? 'Unknown');
                }
            } catch (Exception $e) {
                $errors[] = 'Error processing event: ' . $e->getMessage();
            }
        }

        return ['inserted' => $inserted, 'errors' => $errors];
    }

    private function ingestEbooks($ebooks) {
        $inserted = 0;
        $errors = [];
        
        foreach ($ebooks as $ebookData) {
            try {
                // First, ensure author exists
                $authorId = $this->findOrCreateAuthor($ebookData['author'] ?? []);

                // Create ebook
                $ebookModel = new Ebook($this->conn);
                $data = [
                    'name' => $ebookData['title'] ?? 'Untitled',
                    'category' => $ebookData['category'] ?? 'General',
                    'description' => $ebookData['description'] ?? '',
                    'year_of_publish' => $ebookData['year_of_publish'] ?? date('Y'),
                    'number_of_chapters' => $ebookData['number_of_chapters'] ?? 0,
                    'language' => $ebookData['language'] ?? 'English',
                    'image_url' => $ebookData['image_url'] ?? '',
                    'author_id' => $authorId,
                    'status' => 'draft'
                ];

                if ($ebookModel->create($data)) {
                    $inserted++;
                } else {
                    $errors[] = 'Failed to insert ebook: ' . ($ebookData['title'] ?? 'Unknown');
                }
            } catch (Exception $e) {
                $errors[] = 'Error processing ebook: ' . $e->getMessage();
            }
        }

        return ['inserted' => $inserted, 'errors' => $errors];
    }

    private function ingestNews($news) {
        // For news, just return the data since no table to store
        // In future, could store in a news table
        return ['inserted' => count($news), 'errors' => [], 'data' => $news];
    private function findOrCreateAuthor($authorData) {
        if (empty($authorData['name'])) {
            return null;
        }

        $authorModel = new Author($this->conn);
        
        // Check if author exists
        $query = "SELECT author_id FROM Author WHERE name = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$authorData['name']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            return $existing['author_id'];
        }

        // Create new author
        $data = [
            'name' => $authorData['name'],
            'country' => $authorData['country'] ?? '',
            'no_of_books_published' => $authorData['no_of_books_published'] ?? 0,
            'about' => $authorData['about'] ?? '',
            'website_url' => $authorData['website_url'] ?? '',
            'socialmedia_url' => $authorData['socialmedia_url'] ?? '',
            'image_url' => $authorData['image_url'] ?? '',
            'status' => 'draft'
        ];

        if ($authorModel->create($data)) {
            return $this->conn->lastInsertId();
        }

        return null;
    }

    private function findOrCreatePublisher($publisherData) {
        if (empty($publisherData['name'])) {
            return null;
        }

        $publisherModel = new Publisher($this->conn);
        
        // Check if publisher exists
        $query = "SELECT publisher_id FROM Publisher WHERE name = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$publisherData['name']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            return $existing['publisher_id'];
        }

        // Create new publisher
        $data = [
            'name' => $publisherData['name'],
            'country' => $publisherData['country'] ?? '',
            'website_url' => $publisherData['website_url'] ?? '',
            'image_url' => $publisherData['image_url'] ?? '',
            'status' => 'draft'
        ];

        if ($publisherModel->create($data)) {
            return $this->conn->lastInsertId();
        }

        return null;
    }
}
?>
