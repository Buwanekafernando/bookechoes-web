<?php
abstract class Controller {
    protected $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    protected function getInput() {
        return json_decode(file_get_contents("php://input"), true);
    }
    
    // Abstract method to handle request dispatching if needed, 
    // or just leave it to the Router.
}
?>
