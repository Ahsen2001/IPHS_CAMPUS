<?php
session_start();
include 'db_connect.php';

// ✅ Fetch News Articles
$news_query = "SELECT * FROM news ORDER BY posted_at DESC";
$news_result = $conn->query($news_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>News - IPHS Campus</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="text-center text-primary">📰 Campus News</h2>

    <?php while ($news = $news_result->fetch_assoc()) { ?>
        <div class="card mb-4">
            <?php if (!empty($news['image']) && file_exists($news['image'])) { ?>
                <img src="<?= htmlspecialchars($news['image']); ?>" class="card-img-top" alt="News Image">
            <?php } ?>
            <div class="card-body">
                <h3 class="card-title"><?= htmlspecialchars($news['title']); ?></h3>
                <p class="card-text"><?= nl2br(htmlspecialchars($news['content'])); ?></p>
                <p class="text-muted">📅 Posted on <?= date("F j, Y", strtotime($news['posted_at'])); ?></p>
            </div>
        </div>
    <?php } ?>

</body>
</html>
