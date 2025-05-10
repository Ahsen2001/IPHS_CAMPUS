<?php
session_start();
include 'db_connect.php';
include 'auth.php';

// Ensure only teachers can access
if ($_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Fetch courses assigned to the teacher
$sql = "SELECT id, course_name FROM courses WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$courses_result = $stmt->get_result();

// Handle assignment upload
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $course_id = $_POST['course_id'];
    $deadline = $_POST['deadline'];

    // ✅ File Upload Handling
    $upload_dir = "uploads/assignments/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
    }

    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === 0) {
        $file_name = $_FILES['assignment_file']['name'];
        $file_tmp = $_FILES['assignment_file']['tmp_name'];
        $file_size = $_FILES['assignment_file']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ["pdf", "doc", "docx", "txt"];

        if (!in_array($file_ext, $allowed_exts)) {
            echo "<script>alert('❌ Invalid file type! Only PDF, DOC, DOCX, TXT allowed.');</script>";
        } elseif ($file_size > 5 * 1024 * 1024) { // 5MB limit
            echo "<script>alert('❌ File size exceeds 5MB limit.');</script>";
        } else {
            $new_file_name = time() . "_" . basename($file_name);
            $file_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $file_path)) {
                // ✅ Insert Assignment Data
                $query = "INSERT INTO assignments (title, description, course_id, user_id, deadline, file_path) 
                          VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssiiis", $title, $description, $course_id, $teacher_id, $deadline, $file_path);

                if ($stmt->execute()) {
                    echo "<script>alert('✅ Assignment uploaded successfully!'); window.location='manage_assignments.php';</script>";
                } else {
                    echo "<script>alert('⚠️ Error adding assignment.');</script>";
                }
            } else {
                echo "<script>alert('⚠️ Error uploading file.');</script>";
            }
        }
    } else {
        echo "<script>alert('⚠️ Please upload a valid assignment file.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Assignment</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <h2 class="text-center text-primary">📚 Add New Assignment</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Title:</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description:</label>
                <textarea name="description" class="form-control" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Course:</label>
                <select name="course_id" class="form-select" required>
                    <option value="">Select Course</option>
                    <?php while ($row = $courses_result->fetch_assoc()) { ?>
                        <option value="<?= $row['id']; ?>"><?= htmlspecialchars($row['course_name']); ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Deadline:</label>
                <input type="date" name="deadline" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Upload Assignment (PDF, DOC, DOCX, TXT):</label>
                <input type="file" name="assignment_file" class="form-control" required>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-success">✅ Add Assignment</button>
                <a href="manage_assignments.php" class="btn btn-secondary">🔙 Back</a>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
