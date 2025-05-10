<?php
session_start();
include 'db_connect.php';
include 'auth.php';

// Ensure only teachers can access
if ($_SESSION['role'] !== 'teacher') {
    header("Location:index.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$assignment_id = intval($_GET['id']);

// Check if the assignment belongs to the teacher
$check_sql = "SELECT * FROM assignments WHERE id = '$assignment_id' AND user_id = '$teacher_id'";
$result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($result) > 0) {
    $delete_sql = "DELETE FROM assignments WHERE id = '$assignment_id'";
    if (mysqli_query($conn, $delete_sql)) {
        $_SESSION['success'] = "Assignment deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting assignment.";
    }
} else {
    $_SESSION['error'] = "Unauthorized access or assignment not found.";
}
header("Location: manage_assignments.php");
exit();
?>
