<?php
include '../includes/auth.php';
checkRole('admin');
include 'config.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt->close();
}

header("Location: manage_students.php");
exit();
?>
