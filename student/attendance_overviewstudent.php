<?php
session_start();
include 'db_connect.php';
include 'auth.php';
checkRole('student');

$user_id = $_SESSION['user_id'];

// ✅ Fetch student ID
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

// ✅ Fetch Attendance Data
$attendance_query = "SELECT courses.course_name, attendance.date, attendance.status 
                     FROM attendance 
                     JOIN courses ON attendance.course_id = courses.id 
                     WHERE attendance.student_id = ? 
                     ORDER BY attendance.date DESC";
$stmt = $conn->prepare($attendance_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$attendance_result = $stmt->get_result();

// ✅ Fetch Attendance Percentage
$total_classes_query = "SELECT COUNT(*) AS total FROM attendance WHERE student_id = ?";
$stmt = $conn->prepare($total_classes_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$total_classes_result = $stmt->get_result();
$total_classes = $total_classes_result->fetch_assoc()['total'];

$present_classes_query = "SELECT COUNT(*) AS present FROM attendance WHERE student_id = ? AND status = 'Present'";
$stmt = $conn->prepare($present_classes_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$present_classes_result = $stmt->get_result();
$present_classes = $present_classes_result->fetch_assoc()['present'];

$attendance_percentage = ($total_classes > 0) ? round(($present_classes / $total_classes) * 100, 2) : 0;

// ✅ Set Low Attendance Alert Threshold (e.g., 75%)
$low_attendance = ($attendance_percentage < 75) ? true : false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fd; }
        .container { max-width: 900px; margin-top: 50px; }
        .card { border-radius: 12px; box-shadow: 0px 6px 12px rgba(0,0,0,0.1); transition: 0.3s; }
        .card:hover { transform: scale(1.02); }
        .table thead { background: #023d7d; color: white; }
        .alert-warning { font-weight: bold; }
        .chart-container { width: 100%; max-width: 500px; margin: auto; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-primary text-center">✅ My Attendance</h2>

    <!-- Low Attendance Warning -->
    <?php if ($low_attendance): ?>
        <div class="alert alert-warning text-center">
            ⚠️ Warning: Your attendance is below 75%! Please attend more classes.
        </div>
    <?php endif; ?>

    <!-- Attendance Summary -->
    <div class="card p-4 text-center">
        <h3>📊 Attendance Percentage</h3>
        <h2 class="<?= $low_attendance ? 'text-danger' : 'text-success'; ?>"><?= $attendance_percentage; ?>%</h2>
    </div>

    <!-- Attendance Chart -->
    <div class="chart-container mt-4">
        <canvas id="attendanceChart"></canvas>
    </div>

    <!-- Detailed Attendance Table -->
    <div class="card p-4 mt-4">
        <h3>📅 Attendance Records</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>📚 Course Name</th>
                    <th>📅 Date</th>
                    <th>✅ Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $attendance_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['course_name']); ?></td>
                        <td><?= htmlspecialchars($row['date']); ?></td>
                        <td class="<?= ($row['status'] === 'Present') ? 'text-success' : 'text-danger'; ?>">
                            <?= htmlspecialchars($row['status']); ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Absent'],
            datasets: [{
                data: [<?= $present_classes; ?>, <?= $total_classes - $present_classes; ?>],
                backgroundColor: ['#36A2EB', '#FF6384']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true }
            }
        }
    });
});
</script>

</body>
</html>
