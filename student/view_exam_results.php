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
$student_result = $stmt->get_result();
$student = $student_result->fetch_assoc();
$student_id = $student['id'];

// ✅ Fetch Exam Results (without exam title)
$exam_results_query = "SELECT examst.exam_date, courses.course_name, exam_resultst.marks_obtained, 
                              examst.max_marks, exam_resultst.feedback 
                       FROM exam_resultst 
                       JOIN examst ON exam_resultst.exam_id = examst.id 
                       JOIN courses ON examst.course_id = courses.id 
                       WHERE exam_resultst.student_id = ?";
$stmt = $conn->prepare($exam_results_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$exam_results = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Exam Results</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>📊 My Exam Results</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>📚 Course</th>
                <th>📅 Date</th>
                <th>🔢 Marks</th>
                <th>📊 Max Marks</th>
                <th>💬 Feedback</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($exam = $exam_results->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($exam['course_name']); ?></td>
                    <td><?= htmlspecialchars($exam['exam_date']); ?></td>
                    <td><?= htmlspecialchars($exam['marks_obtained']); ?></td>
                    <td><?= htmlspecialchars($exam['max_marks']); ?></td>
                    <td><?= htmlspecialchars($exam['feedback'] ?? 'No Feedback'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>
