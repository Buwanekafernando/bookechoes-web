<?php
class Response {
    public static function send($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    public static function success($message = "Success", $data = [], $code = 200) {
        self::send([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function error($message = "Error", $code = 400, $errors = []) {
        self::send([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}
?>
