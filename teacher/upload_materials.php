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
$courses_query = "SELECT id, course_name FROM courses WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $courses_query);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$courses_result = mysqli_stmt_get_result($stmt);

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_material'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $course_id = $_POST['course_id'];

    // File Upload Logic
    $target_dir = "uploads/materials/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = basename($_FILES["file"]["name"]);
    $target_file = $target_dir . time() . "_" . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Allowed file types
    $allowed_types = ['pdf', 'docx', 'pptx', 'jpg', 'png'];
    if (!in_array($file_type, $allowed_types)) {
        echo "<script>alert('⚠️ Invalid file type! Allowed: PDF, DOCX, PPTX, JPG, PNG');</script>";
    } elseif ($_FILES["file"]["size"] > 5000000) { // 5MB limit
        echo "<script>alert('⚠️ File too large! Max size: 5MB');</script>";
    } elseif (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        // Insert into database
        $insert_query = "INSERT INTO learning_materials (course_id, user_id, title, description, file_path) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "iisss", $course_id, $teacher_id, $title, $description, $target_file);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('✅ Material uploaded successfully!'); window.location='upload_materials.php';</script>";
        } else {
            echo "<script>alert('⚠️ Error uploading material!');</script>";
        }
    } else {
        echo "<script>alert('⚠️ File upload failed!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Upload Learning Materials</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="text-primary">📂 Upload Learning Materials</h2>

    <form method="POST" enctype="multipart/form-data" class="card p-3 shadow-lg">
        <label class="form-label">📖 Material Title:</label>
        <input type="text" name="title" class="form-control mb-3" required>

        <label class="form-label">📝 Description:</label>
        <textarea name="description" class="form-control mb-3"></textarea>

        <label class="form-label">📚 Select Course:</label>
        <select name="course_id" class="form-control mb-3" required>
            <option value="">-- Select Course --</option>
            <?php while ($course = mysqli_fetch_assoc($courses_result)) { ?>
                <option value="<?php echo $course['id']; ?>"><?php echo $course['course_name']; ?></option>
            <?php } ?>
        </select>

        <label class="form-label">📂 Upload File:</label>
        <input type="file" name="file" class="form-control mb-3" required>

        <button type="submit" name="upload_material" class="btn btn-success">✅ Upload</button>
    </form>
</body>
</html>
