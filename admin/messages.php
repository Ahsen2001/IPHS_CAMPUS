<?php
session_start();
include 'db_connect.php';
include 'auth.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch Users for Messaging (Admins & Teachers)
$users_query = "SELECT id, fullname, role FROM users WHERE id != ?";
$stmt = $conn->prepare($users_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$users_result = $stmt->get_result();

// ✅ Fetch Received Messages
$messages_query = "SELECT messages.id, messages.message, messages.sent_at, users.fullname AS sender_name 
                   FROM messages 
                   JOIN users ON messages.sender_id = users.id
                   WHERE messages.receiver_id = ? 
                   ORDER BY messages.sent_at DESC";
$stmt = $conn->prepare($messages_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$messages_result = $stmt->get_result();

// ✅ Fetch Sent Messages
$sent_query = "SELECT messages.id, messages.message, messages.sent_at, users.fullname AS receiver_name 
               FROM messages 
               JOIN users ON messages.receiver_id = users.id
               WHERE messages.sender_id = ? 
               ORDER BY messages.sent_at DESC";
$stmt = $conn->prepare($sent_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$sent_messages = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Messaging System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fd; }
        .container { max-width: 800px; margin-top: 50px; }
        .card { border-radius: 12px; box-shadow: 0px 6px 12px rgba(0,0,0,0.1); transition: 0.3s; }
        .card:hover { transform: scale(1.02); }
        .message-box { background: #fff; padding: 10px; border-radius: 8px; box-shadow: 0px 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-primary text-center">💬 Messaging System</h2>

    <!-- ✅ Send Message Form -->
    <div class="card p-4 mb-4">
        <h3>📨 Send a Message</h3>
        <form method="POST" action="send_message.php">
            <div class="mb-3">
                <label class="form-label">To:</label>
                <select name="receiver_id" class="form-select" required>
                    <option value="">-- Select Recipient --</option>
                    <?php while ($user = $users_result->fetch_assoc()) { ?>
                        <option value="<?= $user['id']; ?>"><?= htmlspecialchars($user['fullname']) . " (" . ucfirst($user['role']) . ")"; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Message:</label>
                <textarea name="message" class="form-control" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">📤 Send Message</button>
        </form>
    </div>

    <!-- ✅ Inbox (Received Messages) -->
    <div class="card p-4">
        <h3>📩 Inbox</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>📧 From</th>
                    <th>💬 Message</th>
                    <th>📅 Date</th>
                    <th>Reply</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($message = $messages_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($message['sender_name']); ?></td>
                        <td><?= htmlspecialchars($message['message']); ?></td>
                        <td><?= htmlspecialchars($message['sent_at']); ?></td>
                        <td>
                            <a href="replies.php?message_id=<?= $message['id']; ?>" class="btn btn-sm btn-secondary">💬 Reply</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- ✅ Sent Messages -->
    <div class="card p-4 mt-4">
        <h3>📤 Sent Messages</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>📧 To</th>
                    <th>💬 Message</th>
                    <th>📅 Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($sent = $sent_messages->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($sent['receiver_name']); ?></td>
                        <td><?= htmlspecialchars($sent['message']); ?></td>
                        <td><?= htmlspecialchars($sent['sent_at']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
