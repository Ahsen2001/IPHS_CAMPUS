<?php
session_start();
include 'db_connect.php';
include 'auth.php';

checkRole('teacher');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['submission_id'], $_POST['grade'], $_POST['feedback'])) {
        die("<script>alert('⚠️ Missing required fields. Please try again.'); window.history.back();</script>");
    }

    $submission_id = $_POST['submission_id'];
    $grade = trim($_POST['grade']);
    $feedback = trim($_POST['feedback']);

    // Debug: Check values before database update
    if (empty($grade) || empty($feedback)) {
        die("<script>alert('⚠️ Please enter both grade and feedback.'); window.history.back();</script>");
    }

    // ✅ Update the database with grade and feedback
    $query = "UPDATE submissions SET grade = ?, feedback = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        die("<script>alert('⚠️ Database Error: " . $conn->error . "'); window.history.back();</script>");
    }

    $stmt->bind_param("ssi", $grade, $feedback, $submission_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('✅ Grade & feedback submitted successfully!'); window.location='manage_assignments.php';</script>";
    } else {
        echo "<script>alert('⚠️ Error: " . $stmt->error . "'); window.history.back();</script>";
    }
}
?>
