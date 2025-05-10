<?php
session_start();
include 'auth.php';
checkRole('admin');
include 'db_connect.php';

// ✅ Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Invalid teacher ID.");
}
$teacher_id = intval($_GET['id']);

// ✅ Fetch teacher data
$query = "SELECT teachers.*, users.fullname 
          FROM teachers 
          JOIN users ON teachers.user_id = users.id 
          WHERE teachers.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("❌ Teacher not found.");
}
$teacher = $result->fetch_assoc();

// ✅ Handle Update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $qualification = $_POST['qualification'];

    // ✅ Update teachers table
    $update_teacher = "UPDATE teachers SET name = ?, email = ?, subject = ?, qualification = ? WHERE id = ?";
    $stmt1 = $conn->prepare($update_teacher);
    $stmt1->bind_param("ssssi", $name, $email, $subject, $qualification, $teacher_id);

    // ✅ Update users table
    $update_user = "UPDATE users SET fullname = ?, email = ? WHERE id = ?";
    $stmt2 = $conn->prepare($update_user);
    $stmt2->bind_param("ssi", $name, $email, $teacher['user_id']);

    if ($stmt1->execute() && $stmt2->execute()) {
        echo "<script>alert('✅ Teacher updated successfully!'); window.location='manage_teachers.php';</script>";
        exit;
    } else {
        echo "<script>alert('⚠️ Failed to update teacher info.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Teacher</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="text-primary mb-4">✏️ Edit Teacher</h2>

    <form method="POST" class="card p-4 shadow-lg">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($teacher['name']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($teacher['email']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($teacher['subject']); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Qualification</label>
            <input type="text" name="qualification" class="form-control" value="<?= htmlspecialchars($teacher['qualification']); ?>">
        </div>

        <button type="submit" class="btn btn-success">✅ Update Teacher</button>
        <a href="manage_teachers.php" class="btn btn-secondary">↩ Cancel</a>
    </form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
