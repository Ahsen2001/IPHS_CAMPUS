<?php
session_start();
include 'db_connect.php';
include 'auth.php';
checkRole('teacher');

if (!isset($_GET['exam_id'])) {
    die("⚠️ Error: Exam ID not found!");
}

$exam_id = $_GET['exam_id'];
$teacher_id = $_SESSION['user_id'];

// ✅ Fetch exam details
$exam_query = "SELECT title FROM examst WHERE id = ?";
$stmt = $conn->prepare($exam_query);
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam_result = $stmt->get_result();
$exam = $exam_result->fetch_assoc();

// ✅ Fetch students enrolled in the course of this exam
$students_query = "SELECT students.id, students.fullname, 
                          IFNULL(exam_resultst.marks_obtained, '') AS marks_obtained,
                          IFNULL(exam_resultst.feedback, '') AS feedback
                   FROM students
                   JOIN enrollments ON students.id = enrollments.student_id
                   JOIN courses ON enrollments.course_id = courses.id
                   JOIN examst ON courses.id = examst.course_id
                   LEFT JOIN exam_resultst ON students.id = exam_resultst.student_id AND exam_resultst.exam_id = ?
                   WHERE examst.id = ?";
$stmt = $conn->prepare($students_query);
$stmt->bind_param("ii", $exam_id, $exam_id);
$stmt->execute();
$students_result = $stmt->get_result();

// ✅ Handle Grade Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $marks = $_POST['marks'];
    $feedback = $_POST['feedback'];

    // ✅ Insert or Update Grades
    $grade_query = "INSERT INTO exam_resultst (exam_id, student_id, marks_obtained, feedback)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE marks_obtained = VALUES(marks_obtained), feedback = VALUES(feedback)";
    $stmt = $conn->prepare($grade_query);
    $stmt->bind_param("iiis", $exam_id, $student_id, $marks, $feedback);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Grade updated successfully!'); window.location='grade_exam.php?exam_id=$exam_id';</script>";
    } else {
        echo "<script>alert('⚠️ Error updating grade.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Grade Exam - <?= htmlspecialchars($exam['title']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>✏️ Grade Exam: <?= htmlspecialchars($exam['title']) ?></h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>👨‍🎓 Student</th>
                <th>🔢 Marks</th>
                <th>💬 Feedback</th>
                <th>✅ Update</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($student = $students_result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($student['fullname']); ?></td>
                    <td>
                        <form method="POST" class="d-flex">
                            <input type="hidden" name="student_id" value="<?= $student['id']; ?>">
                            <input type="number" name="marks" value="<?= $student['marks_obtained']; ?>" class="form-control me-2" required>
                    </td>
                    <td>
                            <input type="text" name="feedback" value="<?= htmlspecialchars($student['feedback']); ?>" class="form-control me-2">
                    </td>
                    <td>
                            <button type="submit" class="btn btn-success btn-sm">✅ Submit Grade</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>
