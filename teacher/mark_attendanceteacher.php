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

$students_result = null; // Initialize students result

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $attendance_date = $_POST['attendance_date'];
    $students = isset($_POST['students']) ? $_POST['students'] : [];

    if (!empty($students)) {
        foreach ($students as $student_id => $status) {
            $query = "INSERT INTO attendance (student_id, course_id, date, status, marked_by) 
                      VALUES (?, ?, ?, ?, ?) 
                      ON DUPLICATE KEY UPDATE status = VALUES(status)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "iissi", $student_id, $course_id, $attendance_date, $status, $teacher_id);
            if (!mysqli_stmt_execute($stmt)) {
                die("Error: " . mysqli_error($conn)); // Debugging line
            }
        }

        $_SESSION['success'] = "✅ Attendance marked successfully!";
    } else {
        $_SESSION['error'] = "⚠️ No students selected!";
    }

    header("Location: mark_attendance.php");
    exit();
}

// Fetch enrolled students when a course is selected
if (isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];
    $students_query = "SELECT students.id, students.name 
                       FROM students 
                       JOIN enrollments ON students.id = enrollments.student_id 
                       WHERE enrollments.course_id = ?";
    $stmt = mysqli_prepare($conn, $students_query);
    mysqli_stmt_bind_param($stmt, "i", $course_id);
    mysqli_stmt_execute($stmt);
    $students_result = mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="text-primary">📌 Mark Attendance</h2>

    <?php if (isset($_SESSION['success'])) { ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php } ?>
    <?php if (isset($_SESSION['error'])) { ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php } ?>

    <form method="POST" action="" class="card p-3 shadow-lg">
        <label class="form-label">📚 Select Course:</label>
        <select name="course_id" class="form-select mb-3" onchange="this.form.submit()" required>
            <option value="">-- Select Course --</option>
            <?php while ($course = mysqli_fetch_assoc($courses_result)) { ?>
                <option value="<?php echo $course['id']; ?>" <?php echo (isset($_POST['course_id']) && $_POST['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                    <?php echo $course['course_name']; ?>
                </option>
            <?php } ?>
        </select>

        <label class="form-label">📅 Date:</label>
        <input type="date" name="attendance_date" class="form-control mb-3" required>

        <label class="form-label">👩‍🎓 Students:</label><br>
        <div class="border p-3">
        <?php
        if ($students_result && mysqli_num_rows($students_result) > 0) {
            while ($student = mysqli_fetch_assoc($students_result)) { ?>
                <div class="form-check">
                    <input type="hidden" name="students[<?php echo $student['id']; ?>]" value="Absent">
                    <input type="checkbox" class="form-check-input" name="students[<?php echo $student['id']; ?>]" value="Present">
                    <label class="form-check-label"><?php echo $student['name']; ?></label>
                </div>
            <?php }
        } else {
            echo "<p class='text-danger'>No enrolled students found for this course.</p>";
        }
        ?>
        </div>

        <button type="submit" class="btn btn-success mt-3">✅ Submit Attendance</button>
    </form>

</body>
</html>
