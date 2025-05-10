<?php
session_start();
include 'db_connect.php';

$message_id = $_GET['message_id'];
$user_id = $_SESSION['user_id'];

// Fetch the original message
$message_query = "SELECT m.message, fullname AS sender_name 
                  FROM messages m 
                  JOIN users u ON m.sender_id = u.id 
                  WHERE m.id = ?";
$stmt = $conn->prepare($message_query);
$stmt->bind_param("i", $message_id);
$stmt->execute();
$message_result = $stmt->get_result()->fetch_assoc();

// Fetch replies
$replies_query = "SELECT r.reply, r.replied_at,fullname AS sender_name 
                  FROM message_replies r 
                  JOIN users u ON r.sender_id = u.id 
                  WHERE r.message_id = ?
                  ORDER BY r.replied_at ASC";
$stmt = $conn->prepare($replies_query);
$stmt->bind_param("i", $message_id);
$stmt->execute();
$replies_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Replies</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>💬 Message & Replies</h2>
    
    <div class="card p-3 mb-3">
        <strong><?= $message_result['sender_name'] ?>:</strong> <?= $message_result['message'] ?>
    </div>

    <!-- Display Replies -->
    <div class="list-group">
        <?php while ($reply = $replies_result->fetch_assoc()) { ?>
            <div class="list-group-item">
                <strong><?= $reply['sender_name'] ?>:</strong> <?= $reply['reply'] ?>
                <br><small><?= $reply['replied_at'] ?></small>
            </div>
        <?php } ?>
    </div>

    <!-- Reply Form -->
    <form action="send_reply.php" method="POST" class="card p-3 mt-3">
        <input type="hidden" name="message_id" value="<?= $message_id ?>">
        <label>Reply:</label>
        <textarea name="reply" class="form-control mb-2" required></textarea>
        <button type="submit" class="btn btn-success">Reply</button>
    </form>
</body>
</html>
