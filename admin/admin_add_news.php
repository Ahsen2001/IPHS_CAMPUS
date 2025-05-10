<?php
session_start();
include 'db_connect.php';
include 'auth.php';
checkRole('admin'); // Only admins can add news

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);

    // ✅ Handle Image Upload
    $upload_dir = "uploads/news/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $image_path = "";
    if (!empty($_FILES["image"]["name"])) {
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $image_path = $upload_dir . $image_name;

        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
            $image_path = "";
        }
    }

    // ✅ Insert News Article
    $query = "INSERT INTO news (title, content, image) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $title, $content, $image_path);
    
    if ($stmt->execute()) {
        echo "<script>alert('✅ News posted successfully!'); window.location='news.php';</script>";
    } else {
        echo "<script>alert('⚠️ Error posting news.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add News - IPHS Campus</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="text-center text-primary">📰 Add News Article</h2>
    <form method="POST" enctype="multipart/form-data" class="card p-4">
        <label class="form-label">Title:</label>
        <input type="text" name="title" class="form-control mb-3" required>

        <label class="form-label">Content:</label>
        <textarea name="content" class="form-control mb-3" rows="5" required></textarea>

        <label class="form-label">Upload Image:</label>
        <input type="file" name="image" class="form-control mb-3" accept="image/*">

        <button type="submit" class="btn btn-success">✅ Post News</button>
        <a href="news.php" class="btn btn-secondary">🔙 Back</a>
    </form>
</body>
</html>
