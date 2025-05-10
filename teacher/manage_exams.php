<?php
session_start();
include 'db_connect.php';
include 'auth.php';

// Ensure only teachers can access
if ($_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch courses assigned to the teacher
$courses_query = "SELECT id, course_name FROM courses WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $courses_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$courses_result = mysqli_stmt_get_result($stmt);

// Handle exam creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_exam'])) {
    $course_id = $_POST['course_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $exam_date = $_POST['exam_date'];
    $max_marks = $_POST['max_marks'];

    $insert_exam = "INSERT INTO examst (course_id, user_id, title, exam_date, max_marks) 
                    VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_exam);
    mysqli_stmt_bind_param($stmt, "iissi", $course_id, $user_id, $title, $exam_date, $max_marks);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "✅ Exam created successfully!";
    } else {
        $_SESSION['error'] = "⚠️ Error creating exam!";
    }

    header("Location: manage_exams.php");
    exit();
}

// Fetch exams created by the teacher
$examst_query = "SELECT examst.id, examst.title, examst.exam_date, courses.course_name, examst.max_marks 
                FROM examst 
                JOIN courses ON examst.course_id = courses.id 
                WHERE examst.user_id = ?";
$stmt = mysqli_prepare($conn, $examst_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$examst_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Exams</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="text-primary">📌 Manage Exams</h2>

    <?php if (isset($_SESSION['success'])) { ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php } ?>
    <?php if (isset($_SESSION['error'])) { ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php } ?>

    <form method="POST" action="" class="card p-3 shadow-lg">
        <label class="form-label">📚 Select Course:</label>
        <select name="course_id" class="form-select mb-3" required>
            <option value="">-- Select Course --</option>
            <?php while ($course = mysqli_fetch_assoc($courses_result)) { ?>
                <option value="<?php echo $course['id']; ?>"><?php echo $course['course_name']; ?></option>
            <?php } ?>
        </select>

        <label class="form-label">📝 Exam Title:</label>
        <input type="text" name="title" class="form-control mb-3" required>

        <label class="form-label">📅 Exam Date:</label>
        <input type="date" name="exam_date" class="form-control mb-3" required>

        <label class="form-label">🔢 Maximum Marks:</label>
        <input type="number" name="max_marks" class="form-control mb-3" required>

        <button type="submit" name="create_exam" class="btn btn-success">✅ Create Exam</button>
    </form>

    <h3 class="mt-5">📋 Existing Exams</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>📚 Course</th>
                <th>📝 Title</th>
                <th>📅 Date</th>
                <th>🔢 Max Marks</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($exam = mysqli_fetch_assoc($examst_result)) { ?>
                <tr>
                    <td><?php echo $exam['course_name']; ?></td>
                    <td><?php echo $exam['title']; ?></td>
                    <td><?php echo $exam['exam_date']; ?></td>
                    <td><?php echo $exam['max_marks']; ?></td>
                </tr>
                <td>
    <a href="grade_exam.php?exam_id=<?php echo $exam['id']; ?>" class="btn btn-warning btn-sm">✏️ Grade Students</a>
</td>

            <?php } ?>
        </tbody>
    </table>

</body>
</html>
