<?php
class Config {
    // API Keys provided by user
    const GEMINI_API_KEY_MAIN = 'AIzaSyBCdQIoyWK-sFb1iOsn9qMk7ZOXZMhDJaM'; // Books, Authors, Bookshops
    const GEMINI_API_KEY_EVENTS = 'AIzaSyAau6c58z3O26QmocLzgKf8iEhX4uk_8rI'; // Events, News

    public static function getGeminiKey($type) {
        if ($type === 'events' || $type === 'news') {
            return self::GEMINI_API_KEY_EVENTS;
        }
        return self::GEMINI_API_KEY_MAIN;
    }
}
?>
