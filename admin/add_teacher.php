<?php
session_start();
include 'auth.php';
checkRole('admin');
include 'db_connect.php';

// ✅ Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $qualification = trim($_POST['qualification']);
    $default_password = password_hash("teacher123", PASSWORD_DEFAULT);
    $role = "teacher";

    // ✅ Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('❌ Email already exists!'); window.history.back();</script>";
        exit;
    }
    $check->close();

    // ✅ Insert into users table
    $insert_user = $conn->prepare("INSERT INTO users (fullname, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
    $insert_user->bind_param("ssss", $name, $email, $default_password, $role);

    if ($insert_user->execute()) {
        $user_id = $insert_user->insert_id;

        // ✅ Generate teacher_id like TCH001
        $teacher_id = "TCH" . str_pad($user_id, 3, "0", STR_PAD_LEFT);

        // ✅ Insert into teachers table
        $insert_teacher = $conn->prepare("INSERT INTO teachers (user_id, name, email, teacher_id, subject, qualification) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_teacher->bind_param("isssss", $user_id, $name, $email, $teacher_id, $subject, $qualification);

        if ($insert_teacher->execute()) {
            echo "<script>alert('✅ Teacher added successfully!'); window.location='manage_teachers.php';</script>";
        } else {
            echo "<script>alert('❌ Failed to add teacher.');</script>";
        }

        $insert_teacher->close();
    } else {
        echo "<script>alert('❌ Failed to create user.');</script>";
    }

    $insert_user->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Teacher</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="text-primary mb-4">➕ Add New Teacher</h2>

    <form method="POST" class="card p-4 shadow-lg">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Qualification</label>
            <input type="text" name="qualification" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">✅ Add Teacher</button>
        <a href="manage_teachers.php" class="btn btn-secondary">↩ Back</a>
    </form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
