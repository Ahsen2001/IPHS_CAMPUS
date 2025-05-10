<?php
session_start();
include 'auth.php';
checkRole('teacher');
include 'db_connect.php';

$teacher_id = $_SESSION['user_id'];

// Fetch Assigned Courses
$course_query = "SELECT COUNT(*) AS total_courses FROM courses WHERE user_id=?";
$stmt = $conn->prepare($course_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$course_result = $stmt->get_result();
$total_courses = $course_result->fetch_assoc()['total_courses'];

// Fetch Total Students in Assigned Courses
$students_query = "SELECT COUNT(DISTINCT enrollments.student_id) AS total_students 
                   FROM enrollments 
                   JOIN courses ON enrollments.course_id = courses.id 
                   WHERE courses.user_id = ?";
$stmt = $conn->prepare($students_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$students_result = $stmt->get_result();
$total_students = $students_result->fetch_assoc()['total_students'];

// Fetch Assignments Created
$assignments_query = "SELECT COUNT(*) AS total_assignments FROM assignments WHERE user_id=?";
$stmt = $conn->prepare($assignments_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$assignments_result = $stmt->get_result();
$total_assignments = $assignments_result->fetch_assoc()['total_assignments'];

// Fetch Exams Created
$exams_query = "SELECT COUNT(*) AS total_exams FROM examst WHERE user_id=?";
$stmt = $conn->prepare($exams_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$exams_result = $stmt->get_result();
$total_exams = $exams_result->fetch_assoc()['total_exams'];

// Fetch Attendance Taken
$attendance_query = "SELECT COUNT(*) AS total FROM attendance WHERE marked_by=?";
$stmt = $conn->prepare($attendance_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$attendance_result = $stmt->get_result();
$attendance = $attendance_result->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    
    <!-- Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="admin_dashboard.css">

</head>
<body>


<!-- Sidebar Toggle Button -->
<button class="btn btn-outline-primary m-3 d-md-none" id="toggleSidebar">
    <i class="fas fa-bars"></i>
</button>


<!-- Sidebar -->
<div class="sidebar" id="sidebarMenu">
    <img src="../images/logo.jpg" alt="IPHS Logo" class="logo">
    <a href="../index.php"><i class="fas fa-home"></i> Home</a>
    <a href="dashboardteacher.php"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="manage_coursest.php"><i class="fas fa-book"></i> Courses</a>
    <a href="upload_materials.php"><i class="fas fa-folder-open"></i> Learning Materials</a>
    <a href="manage_attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a>
    <a href="manage_exams.php"><i class="fas fa-file-alt"></i> Exams</a>
    <a href="manage_assignments.php"><i class="fas fa-file-alt"></i> Assignments</a>
    <a href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div> 

<!-- Main Content -->
<div class="content">
    <h2 class="text-primary">📌 Teacher Dashboard</h2>

    
    
    <div class="row g-3">
        <div class="col-md-3">
            <div class="card glass-card p-4 text-center">
                <h5 class="text-success">📚 Courses Assigned</h5>
                <h2><?= $total_courses; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card glass-card p-4 text-center">
                <h5 class="text-info">👨‍🎓 Total Students</h5>
                <h2><?= $total_students; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card glass-card p-4 text-center">
                <h5 class="text-warning">📝 Assignments Created</h5>
                <h2><?= $total_assignments; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card glass-card p-4 text-center">
                <h5 class="text-danger">📖 Exams Conducted</h5>
                <h2><?= $total_exams; ?></h2>
            </div>
        </div>
    </div>

    <div class="chart-container mt-4">
        <h3 class="text-primary">📊 Attendance & Exams Overview</h3>
        <canvas id="teacherChart"></canvas>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('teacherChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Attendance Marked', 'Exams Conducted'],
            datasets: [{
                label: 'Statistics',
                data: [<?= $attendance; ?>, <?= $total_exams; ?>],
                backgroundColor: ['#36A2EB', '#FF6384']
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
});
</script>

</body>
</html>
