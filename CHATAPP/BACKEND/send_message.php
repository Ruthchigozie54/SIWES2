<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight OPTIONS requests immediately
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'Chat.php';

// 1. Try to read regular form-data / $_POST data first
$senderId    = isset($_POST['sender_id']) ? $_POST['sender_id'] : null;
$messageText = isset($_POST['message_text']) ? $_POST['message_text'] : null;

// 2. Fallback: If $_POST is empty, check for a JSON raw payload
if (!$senderId || !$messageText) {
    $rawInput = file_get_contents("php://input");
    $jsonData = json_decode($rawInput, true);

    if (!empty($jsonData)) {
        $senderId    = isset($jsonData['sender_id']) ? $jsonData['sender_id'] : $senderId;
        $messageText = isset($jsonData['message_text']) ? $jsonData['message_text'] : $messageText;
    }
}

// 3. Process the message details if they are complete
if ($senderId && $messageText) {
    $chat = new Chat();
    
    // Cast sender_id to an integer explicitly for your OOP class function
    $response = $chat->sendMessage((int)$senderId, $messageText);
    echo json_encode($response);
} else {
    // Return error if details are missing
    http_response_code(400);
    echo json_encode([
        "status" => "error", 
        "message" => "Missing required fields. Please provide sender_id and message_text."
    ]);
}
?>