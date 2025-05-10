<?php
session_start();
include 'db_connect.php';
include 'auth.php';
checkRole('student');

// Get student details
$student_id = $_SESSION['user_id'];
$query = "SELECT fullname, email, profile_pic FROM users WHERE id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = ($result) ? $result->fetch_assoc() : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Student Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    body { background-color: #f4f7fc; font-family: Arial, sans-serif; }
    .sidebar {
      width: 260px;
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      background: #023d7d;
      color: white;
      padding: 20px;
      transition: left 0.3s ease;
      overflow-y: auto;
      z-index: 1000;
    }
    .sidebar a {
      display: block;
      padding: 12px;
      color: white;
      text-decoration: none;
      font-size: 16px;
      border-radius: 5px;
      margin-bottom: 10px;
      transition: 0.3s;
    }
    .sidebar a:hover {
      background-color: white;
      color: #023d7d;
      transform: scale(1.05);
    }
    .sidebar .logo {
      height: 100px;
      width: 120px;
      border-radius: 15px;
      margin-bottom: 1rem;
    }
    .content {
      margin-left: 270px;
      padding: 20px;
    }
    .card {
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    
  .profile-container {
    text-align: center;
    background: linear-gradient(135deg, #f39c12, #e67e22);
    border-radius: 20px;
    padding: 40px 20px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    color: white;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .profile-container:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
  }
  .profile-pic {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    margin-bottom: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }
  .edit {
    display: inline-block;
    margin-top: 15px;
    background-color: #ffffff;
    padding: 8px 20px;
    border-radius: 10px;
    color: #e67e22;
    font-weight: bold;
    text-decoration: none;
    transition: background-color 0.3s ease;
  }
  .edit:hover {
    background-color: #fef2e0;
  }
 

    }
    .hamburger {
      display: none;
      position: fixed;
      top: 10px;
      left: 10px;
      z-index: 1100;
      background: #023d7d;
      color: white;
      border: none;
      padding: 10px;
      border-radius: 5px;
    }
    @media (max-width: 768px) {
      .sidebar { transform: translateX(-100%); }
      .sidebar.show { transform: translateX(0); }
      .content { margin-left: 0; padding-top: 80px; }
      .hamburger { display: block; }
    }
    .sidebar::-webkit-scrollbar,
    .content::-webkit-scrollbar {
      width: 8px;
    }
    .sidebar::-webkit-scrollbar-thumb,
    .content::-webkit-scrollbar-thumb {
      background-color: rgba(0, 0, 0, 0.2);
      border-radius: 4px;
    }
	
	.reset-password {
  display: inline-block;
  margin-top: 30px;
  padding: 10px 20px;
  background: linear-gradient(135deg, #2980b9, #6dd5fa);
  color: white;
  font-weight: bold;
  border-radius: 30px;
  text-decoration: none;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.reset-password:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
  background: linear-gradient(135deg, #3498db, #87e0fd);
}
.reset-password i {
  margin-right: 8px;
}

  </style>
</head>
<body>

<button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

<!-- Sidebar -->
<div class="sidebar" id="sidebarMenu">
  <img src="../images/logo.jpg" alt="IPHS Logo" class="logo">
  <a href="../index.php"><i class="fas fa-home"></i> Home</a>
  <a href="dashboardstudent.php"><i class="fas fa-chart-line"></i> Dashboard</a>
  <a href="enroll_course.php"><i class="fas fa-book"></i> Courses</a>
  <a href="attendance_overviewstudent.php"><i class="fas fa-calendar-check"></i> Attendance</a>
  <a href="view_assignments.php"><i class="fas fa-tasks"></i> Assignments</a>
  <a href="view_exam_results.php"><i class="fas fa-poll"></i> Exam Results</a>
  <a href="timetable_calendar.php"><i class="fas fa-clock"></i> Time Table</a>
  <a href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
  <a href="view_materialsstu.php"><i class="fas fa-book-reader"></i> Notes</a>
  <a href="fees_overviewstudent.php"><i class="fas fa-dollar-sign"></i> Fees</a>
  <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- Content -->
<div class="content">
  <h2>🎓 Welcome, <?= htmlspecialchars($student['fullname']); ?></h2>

  <?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-info"><?= $_SESSION['flash']; unset($_SESSION['flash']); ?></div>
  <?php endif; ?>

  <!-- Profile -->
  <div class="card p-4 profile-container">
    <img src="uploads/<?= htmlspecialchars($student['profile_pic'] ?: 'default.png'); ?>" class="profile-pic" alt="Profile Picture" onerror="this.onerror=null;this.src='uploads/default.png';">
    <h4 class="mt-2"><?= htmlspecialchars($student['fullname']); ?></h4>
    <p>Email: <?= htmlspecialchars($student['email']); ?></p>
    <a href="edit_profile.php" class="edit">Edit Profile</a>
  </div>

  <!-- Dashboard Cards -->
  <div class="row mt-4">
    <div class="col-md-4 col-sm-6">
      <div class="card p-3 text-center">
        <h4>📚 My Courses</h4>
        <a href="enroll_course.php" class="btn btn-primary">View Courses</a>
      </div>
    </div>
	</div>
	 
	<div class="row mt-4">
    <div class="col-md-4 col-sm-6">
      <div class="card p-3 text-center">
        <h4>📅 Attendance</h4>
        <a href="attendance_overviewstudent.php" class="btn btn-primary">Check Attendance</a>
      </div>
    </div>
	 </div>
	<div class="row mt-4">
    <div class="col-md-4 col-sm-6">
      <div class="card p-3 text-center">
        <h4>📝 Exam Results</h4>
        <a href="view_exam_results.php" class="btn btn-primary">View Results</a>
      </div>
    </div>
	 </div>
	<div class="row mt-4">
    <div class="col-md-4 col-sm-6">
      <div class="card p-3 text-center">
        <h4>💰 Fees Overview</h4>
        <a href="fees_overviewstudent.php" class="btn btn-primary">Check Fees</a>
      </div>
    </div>
  </div>

  <a href="edit_profile.php" class="reset-password"><i class="fas fa-key"></i> Reset Password</a>


<script>
  function toggleSidebar() {
    document.getElementById("sidebarMenu").classList.toggle("show");
  }
</script>

</body>
</html>
