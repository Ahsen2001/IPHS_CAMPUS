<?php
include 'db_connect.php';
session_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $course_id = $_POST['course_id'];

    // Handle File Upload
    $document = "default.pdf"; 
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $upload_dir = __DIR__ . "/admin/uploads/"; // ✅ Absolute path for reliability
		$public_dir = "admin/uploads/"; // ✅ For storing relative file path for browser access
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = basename($_FILES['document']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = uniqid("DOC_", true) . "." . $file_ext;
        $target_file = $upload_dir . $new_file_name;

        $allowed_types = ["pdf", "jpg", "jpeg", "png"];
        if (in_array($file_ext, $allowed_types) && move_uploaded_file($_FILES['document']['tmp_name'], $target_file)) {
            $document = $new_file_name;
        }
    }

    // Insert data into the applications table
    $query = "INSERT INTO applications (fullname, email, phone, course_id, document) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssis", $fullname, $email, $phone, $course_id, $document);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Application Submitted Successfully!'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('❌ Error: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
