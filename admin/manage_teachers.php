<?php
session_start();
include 'db_connect.php';
include 'auth.php';
checkRole('admin');

// ✅ Fetch all teachers
$query = "SELECT t.*, u.fullname AS user_name FROM teachers t JOIN users u ON t.user_id = u.id ORDER BY t.id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Teachers</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="text-primary mb-4">👩‍🏫 Manage Teachers</h2>

    <a href="add_teacher.php" class="btn btn-success mb-3">➕ Add New Teacher</a>

    <table class="table table-bordered table-striped text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Teacher ID</th>
                <th>Subject</th>
                <th>Qualification</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td><?= htmlspecialchars($row['teacher_id']); ?></td>
                    <td><?= htmlspecialchars($row['subject']); ?></td>
                    <td><?= htmlspecialchars($row['qualification']); ?></td>
                    <td>
                        <a href="edit_teacher.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                        <a href="delete_teacher.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this teacher?');">🗑️ Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
