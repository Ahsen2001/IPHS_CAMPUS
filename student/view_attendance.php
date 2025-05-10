<?php
session_start();
include '../includes/db_connect.php';
include '../includes/auth.php';

// Ensure only teachers can access
if ($_SESSION['role'] !== 'teacher') {
    header("Location: ../index.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Attendance</title>
</head>
<body>
    <h2>📊 Attendance Records</h2>
    
    <form method="GET" action="">
        <label>📚 Select Course:</label>
        <select name="course_id" required>
            <option value="">-- Select Course --</option>
            <?php
            $courses_query = "SELECT id, course_name FROM courses WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $courses_query);
            mysqli_stmt_bind_param($stmt, "i", $teacher_id);
            mysqli_stmt_execute($stmt);
            $courses_result = mysqli_stmt_get_result($stmt);
            while ($course = mysqli_fetch_assoc($courses_result)) { ?>
                <option value="<?php echo $course['id']; ?>"><?php echo $course['course_name']; ?></option>
            <?php } ?>
        </select>
        <button type="submit">🔍 View</button>
    </form>

    <?php if (isset($_GET['course_id'])) { 
        $course_id = $_GET['course_id'];
        $attendance_query = "SELECT s.name, a.date, a.status 
                             FROM attendance a 
                             JOIN students s ON a.student_id = s.id 
                             WHERE a.course_id = ? 
                             ORDER BY a.date DESC";
        $stmt = mysqli_prepare($conn, $attendance_query);
        mysqli_stmt_bind_param($stmt, "i", $course_id);
        mysqli_stmt_execute($stmt);
        $attendance_result = mysqli_stmt_get_result($stmt);
    ?>
        <table border="1">
            <tr>
                <th>👩‍🎓 Student Name</th>
                <th>📅 Date</th>
                <th>✅ Status</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($attendance_result)) { ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['date']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>
</body>
</html>
