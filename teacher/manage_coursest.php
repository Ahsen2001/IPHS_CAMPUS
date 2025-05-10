<?php
session_start();
include 'db_connect.php';
include 'auth.php';

// Ensure only admins and teachers can access
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// ✅ Fetch courses for Admin and Teacher
if ($role === 'admin') {
    $courses_query = "SELECT courses.id, courses.course_name, courses.course_id, courses.schedule, 
                             users.fullname AS assigned_teacher 
                      FROM courses 
                      LEFT JOIN users ON courses.user_id = users.id";
    $courses_result = mysqli_query($conn, $courses_query);

    // Fetch teachers for assigning courses
    $teachers_query = "SELECT id, fullname FROM users WHERE role = 'teacher'";
    $teachers_result = mysqli_query($conn, $teachers_query);
} else {
    $courses_query = "SELECT id, course_name, course_id, schedule FROM courses WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $courses_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $courses_result = mysqli_stmt_get_result($stmt);
}

// ✅ Handle course addition by admin
if ($role === 'admin' && isset($_POST['add_course'])) {
    $course_name = trim($_POST['course_name']);
    $course_id = strtoupper(trim($_POST['course_id'])); // Ensure uppercase consistency
    $schedule = $_POST['schedule'];
    $teacher_id = $_POST['teacher_id'];

    // ✅ Check if course_id already exists
    $check_query = "SELECT id FROM courses WHERE course_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "s", $course_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        echo "<script>alert('⚠️ Error: Course ID already exists! Choose a different ID.');</script>";
    } else {
        // ✅ Insert the new course
        $insert_query = "INSERT INTO courses (course_name, course_id, schedule, user_id) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "sssi", $course_name, $course_id, $schedule, $teacher_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('✅ Course added successfully!'); window.location='manage_courses.php';</script>";
        } else {
            echo "<script>alert('⚠️ Error adding course.');</script>";
        }
    }
}

// ✅ Handle course deletion by admin
if ($role === 'admin' && isset($_GET['delete_course'])) {
    $course_id = $_GET['delete_course'];
    $delete_query = "DELETE FROM courses WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $course_id);
    mysqli_stmt_execute($stmt);
    echo "<script>alert('✅ Course deleted successfully!'); window.location='manage_courses.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Courses</title>
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
    <h2 class="text-primary">📚 Manage Courses</h2>

    <?php if ($role === 'admin') { ?>
        <form method="post" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="course_name" class="form-control" placeholder="Course Name" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="course_id" class="form-control" placeholder="Course ID (e.g., CS101)" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="schedule" class="form-control" placeholder="Schedule" required>
                </div>
                <div class="col-md-2">
                    <select name="teacher_id" class="form-control" required>
                        <option value="">Assign Teacher</option>
                        <?php while ($teacher = mysqli_fetch_assoc($teachers_result)) { ?>
                            <option value="<?php echo $teacher['id']; ?>"><?php echo $teacher['fullname']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" name="add_course" class="btn btn-primary">➕</button>
                </div>
            </div>
        </form>
    <?php } ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>📚 Course Name</th>
                <th>🆔 Course ID</th>
                <th>📅 Schedule</th>
                <?php if ($role === 'admin') { echo '<th>👨‍🏫 Assigned Teacher</th>'; } ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($course = mysqli_fetch_assoc($courses_result)) { ?>
                <tr>
                    <td><?= $course['course_name']; ?></td>
                    <td><?= $course['course_id']; ?></td>
                    <td><?= $course['schedule']; ?></td>
                    
                </tr>
            <?php } ?>

            
        </tbody>



    </table>
</div>

</body>
</html>
