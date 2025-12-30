<?php
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/../models/User.php';

class Auth {
    // Simple token validation for demonstration. 
    // In production, use JWT or database-backed sessions.
    
    public static function validateToken() {
        $headers = apache_request_headers();
        if (!isset($headers['Authorization'])) {
            Response::error("Unauthorized access. No token provided.", 401);
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        
        // Verify token against database (simple check for now)
        // For real implementation, you'd decode JWT or check `api_tokens` table
        // This is a placeholder for the logic:
        $db = (new Database())->getConnection();
        $userModel = new User($db);
        
        if (!$userModel->validateApiToken($token)) {
            Response::error("Unauthorized access. Invalid token.", 401);
        }
        
        return true;
    }
}
?>
