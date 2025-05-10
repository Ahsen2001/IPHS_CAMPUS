<?php
session_start();
include 'auth.php';
checkRole('admin');
include 'db_connect.php';

// ✅ Check if exam ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $exam_id = intval($_GET['id']);

    // ✅ Prepare deletion query
    $stmt = $conn->prepare("DELETE FROM exams WHERE id = ?");
    $stmt->bind_param("i", $exam_id);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Exam deleted successfully!'); window.location='manage_examadmin.php';</script>";
    } else {
        echo "<script>alert('❌ Error deleting exam.'); window.location='manage_examadmin.php';</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('❌ Invalid exam ID.'); window.location='manage_examadmin.php';</script>";
}

$conn->close();
?>
