<?php
// Authentication.php
require_once 'DbInterface.php';

class Authentication implements DbInterface {
    protected $conn;

    public function __construct() {
        $this->connect();
        // Start session if not already active to track login states
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function connect() {
        $host = "localhost"; 
        $username = "root"; 
        $password = ""; 
        $dbname = "chat_app";

        $this->conn = new mysqli($host, $username, $password, $dbname);

        if ($this->conn->connect_error) {
            http_response_code(500);
            die(json_encode(["error" => "Database connection failed"]));
        }
    }

    // Register User
    public function registerUser($username, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Account created!"];
        } else {
            return ["status" => "error", "message" => "Username or Email already exists"];
        }
    }

    // Login User
    public function loginUser($username, $password) {
        $stmt = $this->conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Verify plain text password against hashed password
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                return [
                    "status" => "success", 
                    "message" => "Logged in successfully",
                    "user" => ["id" => $row['id'], "username" => $row['username']]
                ];
            }
        }
        return ["status" => "error", "message" => "Invalid credentials"];
    }

    // Logout User
    public function logoutUser() {
        session_unset();
        session_destroy();
        return ["status" => "success", "message" => "Logged out successfully"];
    }
}
?>