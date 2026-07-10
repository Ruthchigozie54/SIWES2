<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight OPTIONS requests immediately
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'Authentication.php';

// Try to read regular form-data / $_POST data first
$username = isset($_POST['username']) ? $_POST['username'] : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;

//  If $_POST is empty, check for a JSON raw payload
if (!$username || !$password) {
    $rawInput = file_get_contents("php://input");
    $jsonData = json_decode($rawInput, true);

    if (!empty($jsonData)) {
        $username = isset($jsonData['username']) ? $jsonData['username'] : $username;
        $password = isset($jsonData['password']) ? $jsonData['password'] : $password;
    }
}

// Process login if both credentials are explicitly provided
if ($username && $password) {
    $auth = new Authentication();
    
    // OOP class login function and output the result
    $response = $auth->loginUser($username, $password);
    echo json_encode($response);
} else {
    // Return error if details are missing
    http_response_code(400);
    echo json_encode([
        "status" => "error", 
        "message" => "Missing credentials. Please provide both username and password."
    ]);
}
?>