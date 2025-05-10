<?php
session_start();
include 'auth.php';
checkRole('admin');
include 'db_connect.php';

$total_students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
$total_courses = $conn->query("SELECT COUNT(*) AS total FROM courses")->fetch_assoc()['total'];
$total_exams = $conn->query("SELECT COUNT(*) AS total FROM exams")->fetch_assoc()['total'];
$total_fees_paid = $conn->query("SELECT COUNT(*) AS total FROM fees WHERE status = 'Paid'")->fetch_assoc()['total'];
$total_fees_due = $conn->query("SELECT COUNT(*) AS total FROM fees WHERE status IN ('pending', 'overdue')")->fetch_assoc()['total'];
$total_attendance = $conn->query("SELECT COUNT(*) AS total FROM attendance")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <a href="dashboardadmin.php"><i class="fas fa-chart-line"></i> Dashboard</a>
	<a href="admin_applications.php"><i class="fas fa-file-alt"></i> Applications</a>
	<a href="manage_teachers.php"><i class="fas fa-chalkboard-teacher"></i> Manage Teachers</a>
    <a href="manage_studentsadmin.php"><i class="fas fa-user-graduate"></i> Manage Students</a>
    <a href="manage_courses.php"><i class="fas fa-book"></i> Manage Courses</a>
    <a href="manage_attendance.php"><i class="fas fa-calendar-check"></i> Attendance Report</a>
    <a href="manage_examadmin.php"><i class="fas fa-file-alt"></i> Exam Management</a>
    <a href="messages.php"><i class="fas fa-envelope"></i> Messages</a> 
    <a href="manage_news.php"><i class="fas fa-envelope"></i> News</a> 
    <a href="fees.php"><i class="fas fa-dollar-sign"></i> Fees Management</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="content">
    <h2 class="mb-4 d-flex justify-content-between align-items-center">
        <span><i class="fas fa-chart-line"></i> Admin Dashboard</span>
        <button class="btn btn-sm btn-dark" id="darkToggle"><i class="fas fa-moon"></i> Toggle Dark Mode</button>
    </h2>
	
	<!-- Graph Section -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card p-4">
                <h3>Student Distribution</h3>
                <canvas id="studentChart"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4">
                <h3>Fees Overview</h3>
                <canvas id="feesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white p-4">
                <h4><i class="fas fa-users"></i> Total Students</h4>
                <h2><?= $total_students; ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white p-4">
                <h4><i class="fas fa-book"></i> Total Courses</h4>
                <h2><?= $total_courses; ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white p-4">
                <h4><i class="fas fa-file-alt"></i> Total Exams</h4>
                <h2><?= $total_exams; ?></h2>
            </div>
			
        </div>
    </div>
	
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card bg-danger text-white p-4">
                <h4><i class="fas fa-money"></i> LKR Fees Paid</h4>

                <h2><?= $total_fees_paid; ?></h2>
            </div>
			
        </div>
        <div class="col-md-4">
            <div class="card bg-secondary text-white p-4">
                <h4><i class="fas fa-exclamation-circle"></i> Fees Due</h4>
                <h2><?= $total_fees_due; ?></h2>
            </div>
			
        </div>
		<div class="col-md-4">
    <div class="card bg-info text-white p-4">
        <h4><i class="fas fa-chalkboard-teacher"></i> Total Teachers</h4>
        <h2><?= $conn->query("SELECT COUNT(*) AS total FROM teachers")->fetch_assoc()['total']; ?></h2>
    </div>
</div>
        

    
</div>

<!-- Graph Scripts -->
<script>
var ctx1 = document.getElementById("studentChart").getContext("2d");
var studentChart = new Chart(ctx1, {
    type:"doughnut",
    data: {
        labels: ["Total Students", "Total Courses", "Total Exams"],
        datasets: [{
            data: [<?= $total_students; ?>, <?= $total_courses; ?>, <?= $total_exams; ?>],
            backgroundColor: ["#007bff", "#28a745", "#ffc107"]
        }]
    }
});

var ctx2 = document.getElementById("feesChart").getContext("2d");
var feesChart = new Chart(ctx2, {
    type: "doughnut",
    data: {
        labels: ["Fees Paid", "Fees Due"],
        datasets: [{
            data: [<?= $total_fees_paid; ?>, <?= $total_fees_due; ?>],
            backgroundColor: ["#dc3545", "#6c757d"]
        }]
    }
});

// Sidebar Toggle
document.getElementById("toggleSidebar").addEventListener("click", function () {
    document.getElementById("sidebarMenu").classList.toggle("active");
});

// Dark Mode Toggle
document.getElementById("darkToggle").addEventListener("click", function () {
    document.body.classList.toggle("dark-mode");
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>
