<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Message sent successfully!";
    } else {
        $_SESSION['error'] = "Failed to send message.";
    }

    header("Location: messages.php");
    exit();
}
?>

<!-- HTML: Send Message Form -->
<form method="POST" action="">
    <label>📨 Send Message To:</label>
    <select name="receiver_id" required>
        <option value="1">Admin</option>
        <option value="2">Student 1</option>
        <option value="3">Student 2</option>
    </select>
    <textarea name="message" required></textarea>
    <button type="submit" name="send_message">📩 Send</button>
</form>