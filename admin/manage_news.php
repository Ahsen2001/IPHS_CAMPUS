<?php
session_start();
include 'db_connect.php';
include 'auth.php';
checkRole('admin');

$news_query = "SELECT * FROM news ORDER BY posted_at DESC";
$news_result = $conn->query($news_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage News - IPHS Campus</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="text-center text-primary">📰 Manage News</h2>
    <a href="admin_add_news.php" class="btn btn-success mb-3">➕ Add News</a>

    <table class="table table-bordered text-center">
        <thead class="table-dark">
            <tr>
                <th>Title</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($news = $news_result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($news['title']); ?></td>
                    <td><?= date("F j, Y", strtotime($news['posted_at'])); ?></td>
                    <td>
                        <a href="delete_news.php?id=<?= $news['id']; ?>" class="btn btn-danger btn-sm" 
                           onclick="return confirm('Are you sure?');">🗑 Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>
