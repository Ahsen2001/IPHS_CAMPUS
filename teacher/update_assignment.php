<?php
session_start();
include 'db_connect.php';
include 'auth.php';

// Ensure only teachers can access
if ($_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacher_id = $_SESSION['user_id'];
    $assignment_id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $deadline = $_POST['deadline'];

    // Validate input fields
    if (empty($title) || empty($description) || empty($deadline)) {
        $_SESSION['error'] = "⚠️ Please fill in all fields.";
        header("Location: manage_assignments.php");
        exit();
    }

    // Check if the teacher owns the assignment
    $check_sql = "SELECT id FROM assignments WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "ii", $assignment_id, $teacher_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        // Update assignment using prepared statement
        $update_sql = "UPDATE assignments SET title = ?, description = ?, deadline = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "sssi", $title, $description, $deadline, $assignment_id);

        if (mysqli_stmt_execute($update_stmt)) {
            $_SESSION['success'] = "✅ Assignment updated successfully!";
        } else {
            $_SESSION['error'] = "⚠️ Something went wrong. Please try again.";
        }

        mysqli_stmt_close($update_stmt);
    } else {
        $_SESSION['error'] = "⚠️ Unauthorized access or assignment not found.";
    }

    mysqli_stmt_close($stmt);
    header("Location: manage_assignments.php");
    exit();
}
?>
