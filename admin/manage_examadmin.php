<?php
session_start();
include 'auth.php';
checkRole('admin');
include 'db_connect.php';

// ✅ Fetch admin ID from the `admins` table
$admin_user_id = $_SESSION['user_id']; // Logged-in user ID
$admin_query = $conn->prepare("SELECT id FROM admins WHERE user_id = ?");
$admin_query->bind_param("i", $admin_user_id);
$admin_query->execute();
$admin_result = $admin_query->get_result();
$admin = $admin_result->fetch_assoc();

if (!$admin) {
    echo "<script>alert('❌ Error: Admin record not found! Ensure your account is registered as an admin.');</script>";
    exit();
}

$admin_id = $admin['id']; // Extract the correct admin ID

// ✅ Handle Add Exam
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_exam'])) {
    $course_id = $_POST['course_id'];
    $exam_date = $_POST['exam_date'];
    $total_marks = $_POST['total_marks'];

    // ✅ Insert exam with valid `admin_id`
    $insert_exam = $conn->prepare("INSERT INTO exams (course_id, exam_date, total_marks, admin_id) VALUES (?, ?, ?, ?)");
    $insert_exam->bind_param("issi", $course_id, $exam_date, $total_marks, $admin_id);
    
    if ($insert_exam->execute()) {
        echo "<script>alert('✅ Exam added successfully!'); window.location='manage_examadmin.php';</script>";
    } else {
        echo "<script>alert('❌ Error adding exam: " . $conn->error . "');</script>";
    }
}

// ✅ Fetch Exams
$exam_query = "SELECT exams.id, courses.course_name, exams.exam_date, exams.total_marks, users.fullname AS admin_name
               FROM exams 
               JOIN courses ON exams.course_id = courses.id
               JOIN admins ON exams.admin_id = admins.id
               JOIN users ON admins.user_id = users.id";
$exam_result = $conn->query($exam_query);

// ✅ Fetch Courses
$courses = $conn->query("SELECT id, course_name FROM courses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Exams</title>
    
    <!-- Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="admin_dashboard.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f8f9fd; font-family: 'Poppins', sans-serif; }
        .sidebar { width: 270px; height: 100vh; position: fixed; left: 0; top: 0; background: #023d7d; color: white; padding: 20px; transition: all 0.3s; }
        .sidebar a { display: block; padding: 12px; color: white; text-decoration: none; font-size: 16px; border-radius: 5px; margin-bottom: 10px; transition: 0.3s; }
        .sidebar a:hover { background-color: white; color: #023d7d; transform: scale(1.05); }
        .content { margin-left: 290px; padding: 30px; }
        .card { border-radius: 12px; box-shadow: 0px 6px 12px rgba(0,0,0,0.1); }
        .table thead { background: #023d7d; color: white; }
        .table tbody tr:hover { background: rgba(0, 0, 0, 0.05); }
        .btn { border-radius: 8px; font-size: 16px; transition: 0.3s; }
        .btn:hover { transform: translateY(-2px); }
        .modal-content { border-radius: 10px; }
        .logo { width: 120px; height: auto; border-radius: 10px; margin-bottom: 10px; }
    </style>
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
    <h2 class="mb-4"><i class="fas fa-file-alt"></i> Manage Exams</h2>

    <!-- Add Exam Button -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addExamModal">
        <i class="fas fa-plus"></i> Add Exam
    </button>

    <!-- Exams Table -->
    <div class="card p-4">
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>Course</th>
                    <th>Exam Date</th>
                    <th>Total Marks</th>
                    <th>Admin</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($exam = $exam_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($exam['course_name']); ?></td>
                    <td><?= htmlspecialchars($exam['exam_date']); ?></td>
                    <td><?= htmlspecialchars($exam['total_marks']); ?></td>
                    <td><?= htmlspecialchars($exam['admin_name']); ?></td>
                    <td>
                        <a href="delete_exam.php?id=<?= $exam['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">
                            <i class="fas fa-trash-alt"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Exam Modal -->
<div class="modal fade" id="addExamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Add Exam</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <label>Course</label>
                    <select name="course_id" class="form-control mb-2" required>
                        <?php while ($course = $courses->fetch_assoc()): ?>
                            <option value="<?= $course['id']; ?>"><?= htmlspecialchars($course['course_name']); ?></option>
                        <?php endwhile; ?>
                    </select>

                    <label>Exam Date</label>
                    <input type="date" name="exam_date" class="form-control mb-2" required>

                    <label>Total Marks</label>
                    <input type="number" name="total_marks" class="form-control mb-2" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_exam" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Exam
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
