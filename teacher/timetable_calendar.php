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

// ✅ Fetch Class Timetable for Enrolled Courses
$timetable_query = "SELECT courses.course_name, timetable.day, timetable.start_time, timetable.end_time 
                    FROM timetable 
                    JOIN courses ON timetable.course_id = courses.id 
                    JOIN enrollments ON enrollments.course_id = courses.id 
                    WHERE enrollments.student_id = ? 
                    ORDER BY FIELD(timetable.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
$stmt = $conn->prepare($timetable_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$timetable_result = $stmt->get_result();

// ✅ Fetch Upcoming Exam Dates
$exam_query = "SELECT examst.title, examst.exam_date, courses.course_name 
               FROM examst 
               JOIN courses ON examst.course_id = courses.id 
               JOIN enrollments ON enrollments.course_id = courses.id 
               WHERE enrollments.student_id = ? 
               ORDER BY examst.exam_date ASC";
$stmt = $conn->prepare($exam_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$exam_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>📆 Timetable & Calendar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fd; }
        .container { max-width: 1000px; margin-top: 30px; }
        .card { border-radius: 12px; box-shadow: 0px 6px 12px rgba(0,0,0,0.1); transition: 0.3s; }
        .card:hover { transform: scale(1.02); }
        .table thead { background: #023d7d; color: white; }
        .btn { border-radius: 8px; font-size: 16px; transition: 0.3s; }
        .btn:hover { transform: translateY(-2px); }
        #calendar { max-width: 900px; margin: auto; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-primary text-center">📆 My Timetable & Exam Schedule</h2>

    <!-- Class Timetable -->
    <div class="card p-4 mb-4">
        <h3>📚 Class Timetable</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>📅 Day</th>
                    <th>📚 Course</th>
                    <th>⏰ Start Time</th>
                    <th>⏳ End Time</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $timetable_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['day']); ?></td>
                        <td><?= htmlspecialchars($row['course_name']); ?></td>
                        <td><?= htmlspecialchars($row['start_time']); ?></td>
                        <td><?= htmlspecialchars($row['end_time']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Upcoming Exam Dates -->
    <div class="card p-4 mb-4">
        <h3>📝 Upcoming Exams</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>📚 Course</th>
                    <th>📝 Exam Title</th>
                    <th>📅 Exam Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $exam_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['course_name']); ?></td>
                        <td><?= htmlspecialchars($row['title']); ?></td>
                        <td class="<?= (strtotime($row['exam_date']) < time()) ? 'text-danger' : ''; ?>">
                            <?= htmlspecialchars($row['exam_date']); ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- FullCalendar for Visual Schedule -->
    <div class="card p-4">
        <h3>📅 Calendar View</h3>
        <div id="calendar"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: [
            <?php
                $timetable_result->data_seek(0);
                while ($row = $timetable_result->fetch_assoc()) {
                    echo "{ title: 'Class: " . addslashes($row['course_name']) . "', start: '" . date('Y-m-d') . "', color: '#28a745' },";
                }
                $exam_result->data_seek(0);
                while ($row = $exam_result->fetch_assoc()) {
                    echo "{ title: 'Exam: " . addslashes($row['title']) . "', start: '" . $row['exam_date'] . "', color: '#dc3545' },";
                }
            ?>
        ]
    });
    calendar.render();
});
</script>

</body>
</html>
