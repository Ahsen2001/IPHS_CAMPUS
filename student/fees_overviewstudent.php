<?php
session_start();
include 'db_connect.php';
include 'auth.php';
checkRole('student');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch Student ID
$student_query = "SELECT id FROM students WHERE user_id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("<script>alert('⚠️ Error: Student record not found! Please contact admin.'); window.location='dashboardstudent.php';</script>");
}

$student_id = $student['id'];

// ✅ Fetch the Latest Fee Data
$fees_query = "SELECT fees.amount, fees.status, fees.due_date, 
                      COALESCE(users.fullname, 'Admin') AS updated_by 
               FROM fees 
               LEFT JOIN admins ON fees.admin_id = admins.id
               LEFT JOIN users ON admins.user_id = users.id
               WHERE fees.student_id = ?
               ORDER BY fees.due_date DESC"; 

$stmt = $conn->prepare($fees_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$fees_result = $stmt->get_result();

// ✅ Recalculate Total Fees, Paid, and Due Amounts
$total_fees = 0;
$paid_fees = 0;
$due_fees = 0;
$fees_data = []; // ✅ Initialize to avoid "undefined variable" error

while ($fee = $fees_result->fetch_assoc()) {
    $fees_data[] = $fee;
    $total_fees += $fee['amount'];
    if (strtolower($fee['status']) == "paid") {
        $paid_fees += $fee['amount'];
    }
}

$due_fees = $total_fees - $paid_fees;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Fees</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background-color: #f4f7fc; font-family: Arial, sans-serif; }
        .content { margin-left: 100px; padding: 30px; }
        .card { border-radius: 10px; box-shadow: 0px 4px 8px rgba(0,0,0,0.1); }
        .table thead { background: #023d7d; color: white; }
        .badge-paid { background-color: #28a745; padding: 5px 10px; border-radius: 5px; }
        .badge-due { background-color: #dc3545; padding: 5px 10px; border-radius: 5px; }
    </style>
</head>
<body>

<div class="content">
    <h2 class="mb-4">💰 My Fees Overview</h2>

    <!-- 🔹 Live Fees Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <h4>Total Fees</h4>
                <h3>LKR<?= number_format($total_fees, 2); ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <h4>Paid</h4>
                <h3 class="text-success">LKR<?= number_format($paid_fees, 2); ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <h4>Due</h4>
                <h3 class="text-danger">LKR<?= number_format($due_fees, 2); ?></h3>
            </div>
        </div>
    </div>

    <!-- 🔹 Payment History Table -->
    <div class="card p-4">
        <h3>📋 Payment History</h3>
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Updated By</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($fees_data) > 0): ?>
                    <?php foreach ($fees_data as $fee): ?>
                        <tr>
                            <td>LKR<?= number_format($fee['amount'], 2); ?></td>
                            <td>
                                <span class="badge bg-<?= ($fee['status'] == 'Paid') ? 'success' : (($fee['status'] == 'Pending') ? 'warning' : 'danger'); ?>">
                                    <?= $fee['status']; ?>
                                </span>
                            </td>
                            <td><?= $fee['due_date']; ?></td>
                            <td><?= $fee['updated_by'] ?? "Admin"; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan='4' class='text-center'>No fee records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 🔹 Live Fees Chart -->
    <div class="card p-4 mt-4">
        <h3>📊 Fees Breakdown</h3>
        <canvas id="feesChart"></canvas>
    </div>
</div>

<script>
var ctx = document.getElementById("feesChart").getContext("2d");
var feesChart = new Chart(ctx, {
    type: "pie",
    data: {
        labels: ["Paid", "Due"],
        datasets: [{
            data: [<?= $paid_fees; ?>, <?= $due_fees; ?>],
            backgroundColor: ["#28a745", "#dc3545"]
        }]
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
</body>
</html>
