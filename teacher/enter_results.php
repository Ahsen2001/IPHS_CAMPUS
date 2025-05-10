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

// Fetch exams created by the teacher
$exams_query = "SELECT exams.id, exams.title, courses.course_name 
                FROM exams 
                JOIN courses ON exams.course_id = courses.id 
                WHERE exams.teacher_id = ?";
$stmt = mysqli_prepare($conn, $exams_query);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$exams_result = mysqli_stmt_get_result($stmt);

// Handle result submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_results'])) {
    $exam_id = $_POST['exam_id'];
    $results = $_POST['results'];

    foreach ($results as $student_id => $marks_obtained) {
        $query = "INSERT INTO exam_results (exam_id, student_id, marks_obtained) 
                  VALUES (?, ?, ?) 
                  ON DUPLICATE KEY UPDATE marks_obtained = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iiii", $exam_id, $student_id, $marks_obtained, $marks_obtained);
        mysqli_stmt_execute($stmt);
    }

    $_SESSION['success'] = "✅ Exam results updated successfully!";
    header("Location: enter_results.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Enter Exam Results</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="text-primary">📌 Enter Exam Results</h2>

    <?php if (isset($_SESSION['success'])) { ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php } ?>

    <form method="POST" action="" class="card p-3 shadow-lg">
        <label class="form-label">📚 Select Exam:</label>
        <select name="exam_id" class="form-select mb-3" required onchange="this.form.submit()">
            <option value="">-- Select Exam --</option>
            <?php while ($exam = mysqli_fetch_assoc($exams_result)) { ?>
                <option value="<?php echo $exam['id']; ?>" 
                <?php echo (isset($_POST['exam_id']) && $_POST['exam_id'] == $exam['id']) ? 'selected' : ''; ?>>
                    <?php echo $exam['course_name'] . " - " . $exam['title']; ?>
                </option>
            <?php } ?>
        </select>
    </form>

    <?php
    if (isset($_POST['exam_id'])) {
        $exam_id = $_POST['exam_id'];
        
        // Fetch students enrolled in the course
        $students_query = "SELECT students.id, students.name 
                           FROM students 
                           JOIN enrollments ON students.id = enrollments.student_id 
                           JOIN exams ON enrollments.course_id = exams.course_id 
                           WHERE exams.id = ?";
        $stmt = mysqli_prepare($conn, $students_query);
        mysqli_stmt_bind_param($stmt, "i", $exam_id);
        mysqli_stmt_execute($stmt);
        $students_result = mysqli_stmt_get_result($stmt);

        // Fetch existing results
        $results_query = "SELECT student_id, marks_obtained FROM exam_results WHERE exam_id = ?";
        $stmt = mysqli_prepare($conn, $results_query);
        mysqli_stmt_bind_param($stmt, "i", $exam_id);
        mysqli_stmt_execute($stmt);
        $results_result = mysqli_stmt_get_result($stmt);
        $existing_results = [];
        while ($row = mysqli_fetch_assoc($results_result)) {
            $existing_results[$row['student_id']] = $row['marks_obtained'];
        }
    ?>

    <form method="POST" action="" class="card p-3 shadow-lg">
        <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">

        <h4>👩‍🎓 Students</h4>
        <?php while ($student = mysqli_fetch_assoc($students_result)) { ?>
            <label><?php echo $student['name']; ?></label>
            <input type="number" name="results[<?php echo $student['id']; ?>]" 
                   value="<?php echo $existing_results[$student['id']] ?? ''; ?>" 
                   class="form-control mb-2" required>
        <?php } ?>

        <button type="submit" name="submit_results" class="btn btn-success">✅ Submit Results</button>
    </form>

    <?php } ?>

</body>
</html>
