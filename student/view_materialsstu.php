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

$student_id = $student['id']; // Get student ID

// ✅ Fetch Learning Materials for Enrolled Courses
$materials_query = "SELECT learning_materials.title, learning_materials.file_path, courses.course_name 
                    FROM learning_materials 
                    JOIN courses ON learning_materials.course_id = courses.id 
                    JOIN enrollments ON enrollments.course_id = courses.id
                    WHERE enrollments.student_id = ?
                    ORDER BY learning_materials.uploaded_at DESC";
$stmt = $conn->prepare($materials_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$materials_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Learning Materials</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="text-primary text-center">📚 Learning Materials</h2>

    <div class="card p-4">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>📚 Course</th>
                    <th>📄 Title</th>
                    <th>📥 Download</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $materials_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['course_name']); ?></td>
                        <td><?= htmlspecialchars($row['title']); ?></td>
                        <td>
                            <?php if (!empty($row['file_path']) && file_exists($row['file_path'])) { ?>
                                <a href="<?= htmlspecialchars($row['file_path']); ?>" download class="btn btn-success btn-sm">📥 Download</a>
                            <?php } else { ?>
                                <span class="text-danger">File Not Found</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
