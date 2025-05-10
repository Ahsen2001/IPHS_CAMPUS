<?php
session_start();
include 'db_connect.php';
include 'auth.php';
checkRole('student');

$user_id = $_SESSION['user_id'];

// ✅ Ensure student exists in `students` table
$student_query = "SELECT id FROM students WHERE user_id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("<script>alert('⚠️ Error: Student record not found! Please contact admin.'); window.location='dashboard_student.php';</script>");
}

$student_id = $student['id']; // Get correct student ID

// ✅ Fetch available courses
$available_courses_query = "SELECT id, course_name FROM courses 
                            WHERE id NOT IN (SELECT course_id FROM enrollments WHERE student_id = ?)";
$stmt = $conn->prepare($available_courses_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$available_courses = $stmt->get_result();

// ✅ Fetch enrolled courses
$enrolled_courses_query = "SELECT courses.course_name, courses.schedule 
                           FROM enrollments 
                           JOIN courses ON enrollments.course_id = courses.id 
                           WHERE enrollments.student_id = ?";
$stmt = $conn->prepare($enrolled_courses_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$enrolled_courses = $stmt->get_result();

// ✅ Handle course enrollment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enroll_course'])) {
    $course_id = $_POST['course_id'];

    $enroll_query = "INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)";
    $stmt = $conn->prepare($enroll_query);
    $stmt->bind_param("ii", $student_id, $course_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('✅ Enrollment Successful!'); window.location='enroll_course.php';</script>";
    } else {
        echo "<script>alert('⚠️ Error: Enrollment failed.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Enroll in Courses</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fd; }
        .container { max-width: 800px; margin-top: 50px; }
        .card { border-radius: 12px; box-shadow: 0px 6px 12px rgba(0,0,0,0.1); transition: 0.3s; }
        .card:hover { transform: scale(1.02); }
        .btn { border-radius: 8px; font-size: 16px; transition: 0.3s; }
        .btn:hover { transform: translateY(-2px); }
        .table thead { background: #023d7d; color: white; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-primary text-center">📚 Enroll in Courses</h2>

    <!-- Course Enrollment Form -->
    <div class="card p-4 mb-4">
        <h3>Select a Course to Enroll</h3>
        <form method="POST">
            <div class="mb-3">
                <select name="course_id" class="form-select" required>
                    <option value="">-- Select Course --</option>
                    <?php while ($course = $available_courses->fetch_assoc()) { ?>
                        <option value="<?= $course['id']; ?>"><?= htmlspecialchars($course['course_name']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" name="enroll_course" class="btn btn-primary">✅ Enroll</button>
        </form>
    </div>

    <!-- Enrolled Courses Table -->
    <div class="card p-4">
        <h3>📖 My Enrolled Courses</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>📚 Course Name</th>
                    <th>📅 Schedule</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($course = $enrolled_courses->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($course['course_name']); ?></td>
                        <td><?= htmlspecialchars($course['schedule']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
