<?php
// Chat.php
require_once 'DbInterface.php';

class Chat implements DbInterface {
    protected $conn;

    // Change this if your frontend URL is different
    private $appUrl = "https://ruth.alwaysdata.net/CHATAPP/FRONTEND/";

    
    // under E-mails > Addresses in the alwaysdata panel. Improves deliverability.
    private $fromAddress = "no-reply@ruth.alwaysdata.net";

    // Minimum minutes between two notification emails to the same person,
    // no matter how many messages get posted in between.
    private $throttleMinutes = 5;

    public function __construct() {
        $this->connect();
    }

    public function connect() {
        $host = "mysql-ruth.alwaysdata.net"; 
        $username = "ruth_chigozie"; 
        $password = "ru2th4.ch1"; 
        $dbname = "ruth_chatapp";

        try {
            mysqli_report(MYSQLI_REPORT_OFF);
            $this->conn = new mysqli($host, $username, $password, $dbname);

            if ($this->conn->connect_error) {
                http_response_code(500);
                die(json_encode(["status" => "error", "message" => "Database connection failed"]));
            }
        } catch (Exception $e) {
            http_response_code(500);
            die(json_encode(["status" => "error", "message" => "Critical DB Error: " . $e->getMessage()]));
        }
    }

    // Post a message to the group
    public function sendMessage($senderId, $messageText) {
        $stmt = $this->conn->prepare("INSERT INTO group_messages (sender_id, message_text) VALUES (?, ?)");
        $stmt->bind_param("is", $senderId, $messageText);

        if ($stmt->execute()) {
            // Notify everyone else by email. Wrapped so a mail failure
            // never breaks the actual chat response.
            try {
                $this->notifyOtherUsers($senderId, $messageText);
            } catch (Exception $e) {
                error_log("Email notification failed: " . $e->getMessage());
            }
            return ["status" => "success", "message" => "Message sent"];
        } else {
            return ["status" => "error", "message" => "Could not deliver message"];
        }
    }

    // Email every registered user except the sender that a new message arrived,
    // skipping anyone who was already notified within the throttle window.
    private function notifyOtherUsers($senderId, $messageText) {
        // Look up the sender's username for the email content
        $senderStmt = $this->conn->prepare("SELECT username FROM users WHERE id = ?");
        $senderStmt->bind_param("i", $senderId);
        $senderStmt->execute();
        $senderRow = $senderStmt->get_result()->fetch_assoc();
        $senderName = $senderRow ? $senderRow['username'] : "Someone";

        // Get every other user's id, email, username, and last notified time
        $stmt = $this->conn->prepare("SELECT id, email, username, last_notified_at FROM users WHERE id != ?");
        $stmt->bind_param("i", $senderId);
        $stmt->execute();
        $result = $stmt->get_result();

        $subject = "New message from $senderName on Wall";
        $preview = mb_strimwidth($messageText, 0, 200, "...");
        $throttleSeconds = $this->throttleMinutes * 60;
        $now = new DateTime();

        $updateStmt = $this->conn->prepare("UPDATE users SET last_notified_at = NOW() WHERE id = ?");

        while ($row = $result->fetch_assoc()) {
            $to = $row['email'];
            if (!$to) continue;

            // Skip this person if they were notified too recently
            if ($row['last_notified_at']) {
                $lastNotified = new DateTime($row['last_notified_at']);
                $secondsSince = $now->getTimestamp() - $lastNotified->getTimestamp();
                if ($secondsSince < $throttleSeconds) {
                    continue;
                }
            }

            $body  = "Hi {$row['username']},\n\n";
            $body .= "$senderName just posted a new message in the group chat:\n\n";
            $body .= "\"$preview\"\n\n";
            $body .= "Open the chat to reply: {$this->appUrl}\n";

            $headers  = "From: {$this->fromAddress}\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            // @ suppresses warnings if a send fails for one recipient;
            // we don't want one bad address to stop the loop.
            $sent = @mail($to, $subject, $body, $headers);

            if ($sent) {
                $userId = $row['id'];
                $updateStmt->bind_param("i", $userId);
                $updateStmt->execute();
            }
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