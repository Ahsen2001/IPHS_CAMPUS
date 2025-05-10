<?php
session_start();
require_once 'db_connect.php';
require_once 'auth.php';

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// ✅ Fetch Courses Based on Role
if ($role === 'admin') {
    $courseQuery = "SELECT id, course_name FROM courses";
    $courseStmt = $conn->prepare($courseQuery);
} else {
    $courseQuery = "SELECT id, course_name FROM courses WHERE user_id = ?";
    $courseStmt = $conn->prepare($courseQuery);
    $courseStmt->bind_param("i", $user_id);
}
$courseStmt->execute();
$courses = $courseStmt->get_result();

// ✅ Fetch Enrolled Students for Teachers
if ($role === 'teacher') {
    $studentQuery = "SELECT DISTINCT students.id, students.fullname 
                     FROM students 
                     JOIN enrollments ON students.id = enrollments.student_id 
                     JOIN courses ON enrollments.course_id = courses.id 
                     WHERE courses.user_id = ?";
    $studentStmt = $conn->prepare($studentQuery);
    $studentStmt->bind_param("i", $user_id);
    $studentStmt->execute();
    $students = $studentStmt->get_result();
} else {
    $studentQuery = "SELECT id, fullname FROM students";
    $students = $conn->query($studentQuery);
}

// ✅ Handle Attendance Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_attendance'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $attendance_status = $_POST['attendance_status'];
    $date = date('Y-m-d');

    // Check if attendance is already marked
    $checkQuery = "SELECT * FROM attendance WHERE student_id = ? AND course_id = ? AND date = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("iis", $student_id, $course_id, $date);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('⚠️ Attendance already marked for this student today.');</script>";
    } else {
        $insertQuery = "INSERT INTO attendance (student_id, course_id, status, date, marked_by) VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("iissi", $student_id, $course_id, $attendance_status, $date, $user_id);
        
        if ($insertStmt->execute()) {
            echo "<script>alert('✅ Attendance marked successfully!');</script>";
        } else {
            echo "<script>alert('⚠️ Error marking attendance.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-lg p-4">
            <h2 class="text-center text-primary">📅 Manage Attendance</h2>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">📚 Select Course:</label>
                    <select name="course_id" class="form-control" required>
                        <option value="">-- Select Course --</option>
                        <?php while ($course = $courses->fetch_assoc()): ?>
                            <option value="<?= $course['id']; ?>"><?= htmlspecialchars($course['course_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">👨‍🎓 Select Student:</label>
                    <select name="student_id" class="form-control" required>
                        <option value="">-- Select Student --</option>
                        <?php while ($student = $students->fetch_assoc()): ?>
                            <option value="<?= $student['id']; ?>"><?= htmlspecialchars($student['fullname']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">✅ Attendance Status:</label>
                    <select name="attendance_status" class="form-control" required>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                    </select>
                </div>

                <button type="submit" name="mark_attendance" class="btn btn-primary w-100">📌 Mark Attendance</button>
            </form>
        </div>

        <div class="card shadow-lg p-4 mt-5">
            <h2 class="text-center text-success">📜 Attendance Records</h2>
            <table class="table table-bordered table-hover mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>👨‍🎓 Student</th>
                        <th>📚 Course</th>
                        <th>✅ Status</th>
                        <th>📅 Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $attendanceQuery = "SELECT students.fullname AS student_name, courses.course_name AS course_name, 
                                       attendance.status, attendance.date 
                                       FROM attendance 
                                       JOIN students ON attendance.student_id = students.id 
                                       JOIN courses ON attendance.course_id = courses.id";
                    if ($role === 'teacher') {
                        $attendanceQuery .= " WHERE courses.user_id = ?";
                        $attendanceStmt = $conn->prepare($attendanceQuery);
                        $attendanceStmt->bind_param("i", $user_id);
                        $attendanceStmt->execute();
                        $attendanceResults = $attendanceStmt->get_result();
                    } else {
                        $attendanceResults = $conn->query($attendanceQuery);
                    }
                    while ($row = $attendanceResults->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['student_name']); ?></td>
                            <td><?= htmlspecialchars($row['course_name']); ?></td>
                            <td class="<?= $row['status'] == 'Present' ? 'text-success' : 'text-danger'; ?>">
                                <?= htmlspecialchars($row['status']); ?>
                            </td>
                            <td><?= htmlspecialchars($row['date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
