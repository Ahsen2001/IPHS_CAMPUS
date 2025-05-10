<?php
session_start();
include 'db_connect.php';
include 'auth.php';
checkRole('admin'); // Only admins can delete news

if (isset($_GET['id'])) {
    $news_id = $_GET['id'];

    // ✅ Fetch Image Path to Delete
    $query = "SELECT image FROM news WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $news_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $news = $result->fetch_assoc();

    // ✅ Delete Image from Server
    if (!empty($news['image']) && file_exists($news['image'])) {
        unlink($news['image']);
    }

    // ✅ Delete News from Database
    $delete_query = "DELETE FROM news WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $news_id);

    if ($stmt->execute()) {
        echo "<script>alert('✅ News deleted successfully!'); window.location='news.php';</script>";
    } else {
        echo "<script>alert('⚠️ Error deleting news.');</script>";
    }
}
?>
