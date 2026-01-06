<?php
require_once __DIR__ . '/../config/Config.php';

class GeminiService {
    private $apiKey;

    public function __construct($type = 'general') {
        $this->apiKey = Config::getGeminiKey($type);
    }

    private function callGemini($prompt) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $this->apiKey;

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "response_mime_type" => "application/json"
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            return ['error' => 'Curl error: ' . curl_error($ch)];
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['error' => 'Gemini API Error (' . $httpCode . '): ' . $response];
        }

        $decoded = json_decode($response, true);
        if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            $jsonText = $decoded['candidates'][0]['content']['parts'][0]['text'];
            return json_decode($jsonText, true);
        }

        return ['error' => 'Invalid response format from Gemini'];
    }

    public function fetchBooks($topic = "latest fiction") {
        $prompt = "Generate a JSON array of 5 distinct '$topic' books. 
        Each object must strictly follow this structure:
        {
            \"title\": \"string\",
            \"category\": \"string\",
            \"description\": \"string (summary)\",
            \"year_of_publish\": int,
            \"number_of_chapters\": int (estimate),
            \"language\": \"string\",
            \"image_url\": \"string (valid URL to book cover)\",
            \"author\": {
                \"name\": \"string\",
                \"about\": \"string\",
                \"country\": \"string\"
            },
            \"publisher\": {
                \"name\": \"string\",
                \"country\": \"string\"
            }
        }
        Ensure image_url is a real, publicly accessible URL if possible, or use a high-quality placeholder from a reliable source like covers.openlibrary.org or similar.";
        
        return $this->callGemini($prompt);
    }

    public function fetchAuthors($genre = "famous") {
        $prompt = "Generate a JSON array of 3 '$genre' authors.
        Each object must follow:
        {
            \"name\": \"string\",
            \"country\": \"string\",
            \"no_of_books_published\": int,
            \"about\": \"string\",
            \"website_url\": \"string\",
            \"socialmedia_url\": \"string\",
            \"image_url\": \"string (valid URL to author photo)\"
        }";
        return $this->callGemini($prompt);
    }
    
    public function fetchBookshops($location = "global") {
        $prompt = "Generate a JSON array of 3 famous bookshops in '$location'.
        Structure:
        {
            \"name\": \"string\",
            \"location\": \"string (address)\",
            \"country\": \"string\",
            \"description\": \"string\",
            \"image_url\": \"string\"
        }";
        return $this->callGemini($prompt);
    }

    public function fetchEvents() {
        $prompt = "Generate a JSON array of 3 upcoming major literary events.
        Structure:
        {
            \"name\": \"string\",
            \"location\": \"string\",
            \"description\": \"string\",
            \"date_start\": \"YYYY-MM-DD HH:MM:SS\",
            \"date_end\": \"YYYY-MM-DD HH:MM:SS\",
            \"image_url\": \"string\"
        }";
        return $this->callGemini($prompt);
    }
}
?>
