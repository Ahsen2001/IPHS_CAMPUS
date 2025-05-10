<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message_id = $_POST['message_id'];
    $sender_id = $_SESSION['user_id'];
    $reply = $_POST['reply'];

    $stmt = $conn->prepare("INSERT INTO message_replies (message_id, sender_id, reply) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $message_id, $sender_id, $reply);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Reply sent successfully!";
    } else {
        $_SESSION['error'] = "Failed to send reply.";
    }

    header("Location: replies.php?message_id=" . $message_id);
    exit();
}
?>
