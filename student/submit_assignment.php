<?php
session_start();
include 'db_connect.php';
include 'auth.php';
checkRole('student');

$user_id = $_SESSION['user_id'];

// ✅ Fetch Student ID
$student_query = "SELECT id FROM students WHERE user_id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("<script>alert('⚠️ Error: Student record not found! Please contact admin.'); window.location='dashboard_student.php';</script>");
}

$student_id = $student['id']; 

// ✅ Handle File Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["submission_file"])) {
    $assignment_id = $_POST['assignment_id'];
    
    // ✅ File Upload Directory
    $upload_dir = "uploads/submissions/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
    }

    // ✅ File Information
    $file_name = $_FILES['submission_file']['name'];
    $file_tmp = $_FILES['submission_file']['tmp_name'];
    $file_size = $_FILES['submission_file']['size'];
    $file_error = $_FILES['submission_file']['error'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_exts = ["pdf", "doc", "docx"];

    echo "<pre>DEBUG INFO:
    File Name: $file_name
    Temp Path: $file_tmp
    File Size: $file_size
    File Error: $file_error
    </pre>";

    if (!in_array($file_ext, $allowed_exts)) {
        die("<script>alert('❌ Invalid file type! Only PDF, DOC, DOCX allowed.'); window.history.back();</script>");
    }

    if ($file_size > 5 * 1024 * 1024) { // 5MB limit
        die("<script>alert('❌ File size exceeds 5MB limit.'); window.history.back();</script>");
    }

    if ($file_error !== 0) {
        die("<script>alert('❌ Error uploading file! Error Code: $file_error'); window.history.back();</script>");
    }

    // ✅ Save File
    $new_file_name = time() . "_" . basename($file_name);
    $file_path = $upload_dir . $new_file_name;

    if (move_uploaded_file($file_tmp, $file_path)) {
        echo "<script>alert('✅ File uploaded successfully: $file_path');</script>";

        // ✅ Insert Submission Data
        $insert_query = "INSERT INTO submissions (assignment_id, student_id, user_id, file_path) 
                         VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iiis", $assignment_id, $student_id, $user_id, $file_path);

        if ($stmt->execute()) {
            echo "<script>alert('✅ Submission recorded successfully!'); window.location='view_assignments.php';</script>";
        } else {
            echo "<script>alert('⚠️ Error saving submission: " . $stmt->error . "');</script>";
        }
    } else {
        die("<script>alert('❌ Error moving uploaded file!'); window.history.back();</script>");
    }
} else {
    die("<script>alert('⚠️ No file selected!'); window.history.back();</script>");
}
?>
