<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

class AuthController extends Controller {
    private $user;

    public function __construct($db) {
        parent::__construct($db);
        $this->user = new User($db);
    }

    public function login() {
        $data = $this->getInput();
        $errors = Validator::validateRequired($data, ['email', 'password']);
        
        if (!empty($errors)) {
            Response::error("Validation Error", 400, $errors);
        }

        $loggedInUser = $this->user->login($data['email'], $data['password']);

        if ($loggedInUser) {
            Response::success("Login successful", $loggedInUser);
        } else {
            Response::error("Invalid credentials", 401);
        }
    }

    public function register() {
        // Optional: Admin registration endpoint
        $data = $this->getInput();
        $errors = Validator::validateRequired($data, ['username', 'email', 'password']);

        if (!empty($errors)) {
            Response::error("Validation Error", 400, $errors);
        }

        if ($this->user->create($data['username'], $data['email'], $data['password'])) {
            Response::success("User registered successfully");
        } else {
            Response::error("Unable to register user", 503);
        }
    }
}
?>
