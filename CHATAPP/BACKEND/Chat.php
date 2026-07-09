<?php
// Chat.php
require_once 'DbInterface.php';

class Chat implements DbInterface {
    protected $conn;

    public function __construct() {
        $this->connect();
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

    // Post a message to the group
    public function sendMessage($senderId, $messageText) {
        $stmt = $this->conn->prepare("INSERT INTO group_messages (sender_id, message_text) VALUES (?, ?)");
        $stmt->bind_param("is", $senderId, $messageText);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Message sent"];
        } else {
            return ["status" => "error", "message" => "Could not deliver message"];
        }
    }

    // Fetch the entire group feed history, linking usernames to the messages
    public function getGroupMessages() {
        $query = "
            SELECT gm.id, gm.message_text, gm.created_at, u.username, gm.sender_id 
            FROM group_messages gm 
            JOIN users u ON gm.sender_id = u.id 
            ORDER BY gm.created_at ASC
        ";
        $result = $this->conn->query($query);
        $messages = [];

        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }

        return ["status" => "success", "messages" => $messages];
    }
}
?>