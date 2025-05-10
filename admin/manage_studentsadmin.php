<?php
session_start();
include 'auth.php';
checkRole('admin'); 
include 'db_connect.php';

// ✅ Handle Add Student
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_student'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $class = trim($_POST['class']);
    $guardian_name = trim($_POST['guardian_name']);

    // ✅ Check if Email Already Exists
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();
    if ($check_email->num_rows > 0) {
        echo "<script>alert('Error: Email already exists!'); window.location='manage_studentsadmin.php';</script>";
        exit();
    }
    $check_email->close();

    // ✅ Insert into Users Table
    $user_query = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, 'student')");
    $user_query->bind_param("sss", $fullname, $email, $password);
    
    if ($user_query->execute()) {
        $user_id = $conn->insert_id; // Get newly inserted user ID

        // ✅ Insert into Students Table
        $student_query = $conn->prepare("INSERT INTO students (user_id, class, guardian_name) VALUES (?, ?, ?)");
        $student_query->bind_param("iss", $user_id, $class, $guardian_name);
        $student_query->execute();

        echo "<script>alert('Student added successfully!'); window.location='manage_studentsadmin.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error adding student: " . $conn->error . "');</script>";
    }
}

// ✅ Fetch Students
$students_query = "SELECT students.id, users.fullname, users.email, students.class, students.guardian_name 
                   FROM students JOIN users ON students.user_id = users.id";
$students_result = $conn->query($students_query);

// ✅ Handle Delete Student
if (isset($_GET['delete'])) {
    $student_id = intval($_GET['delete']);

    // Get the user_id associated with the student
    $get_user_id = $conn->prepare("SELECT user_id FROM students WHERE id = ?");
    $get_user_id->bind_param("i", $student_id);
    $get_user_id->execute();
    $result = $get_user_id->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];

        // Delete student first
        $delete_student = $conn->prepare("DELETE FROM students WHERE id = ?");
        $delete_student->bind_param("i", $student_id);
        $delete_student->execute();

        // Then delete the user (Prevents FK constraint error)
        $delete_user = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delete_user->bind_param("i", $user_id);
        $delete_user->execute();

        echo "<script>alert('Student deleted successfully!'); window.location='manage_studentsadmin.php';</script>";
    } else {
        echo "<script>alert('Error: Student not found!');</script>";
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<link rel="stylesheet" href="admin_dashboard.css">

    
</head>
<body>

<div class="sidebar" id="sidebarMenu">
    <img src="../images/logo.jpg" alt="IPHS Logo" class="logo">
    <a href="../index.php"><i class="fas fa-home"></i> Home</a>
    <a href="dashboardadmin.php"><i class="fas fa-chart-line"></i> Dashboard</a>
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
    <h2 class="mb-4">Manage Students</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addStudentModal">➕ Add Student</button>

    <div class="card p-4">
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Class</th>
                    <th>Guardian</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($student = $students_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($student['id']); ?></td>
                    <td><?= htmlspecialchars($student['fullname']); ?></td>
                    <td><?= htmlspecialchars($student['email']); ?></td>
                    <td><?= htmlspecialchars($student['class']); ?></td>
                    <td><?= htmlspecialchars($student['guardian_name']); ?></td>
                    <td>
                        <a href="manage_studentsadmin.php?delete=<?= $student['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">🗑 Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <label>Full Name</label>
                    <input type="text" name="fullname" class="form-control mb-2" required>
                    <label>Email</label>
                    <input type="email" name="email" class="form-control mb-2" required>
                    <label>Password</label>
                    <input type="password" name="password" class="form-control mb-2" required>
                    <label>Class</label>
                    <input type="text" name="class" class="form-control mb-2" required>
                    <label>Guardian Name</label>
                    <input type="text" name="guardian_name" class="form-control mb-2" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>
 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
