<?php
session_start();
include 'db_connect.php'; // Adjust path as needed
include 'auth.php';
checkRole('admin');

if (isset($_GET['id'])) {
    $teacher_id = intval($_GET['id']);

    // First, fetch the user_id related to this teacher
    $query = "SELECT user_id FROM teachers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacher = $result->fetch_assoc();

    if ($teacher) {
        $user_id = $teacher['user_id'];

        // Delete from teachers table
        $stmt1 = $conn->prepare("DELETE FROM teachers WHERE id = ?");
        $stmt1->bind_param("i", $teacher_id);
        $stmt1->execute();

        // Delete from users table
        $stmt2 = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();

        echo "<script>alert('✅ Teacher deleted successfully!'); window.location='manage_teachers.php';</script>";
    } else {
        echo "<script>alert('❌ Teacher not found!'); window.location='manage_teachers.php';</script>";
    }
} else {
    echo "<script>alert('❌ Invalid request!'); window.location='manage_teachers.php';</script>";
}
$conn->close();
?>
