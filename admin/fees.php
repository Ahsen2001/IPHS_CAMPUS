<?php
session_start();
include 'auth.php';
checkRole('admin');  
include 'db_connect.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fees Management</title>
    
    <!-- Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f4f7fc; font-family: Arial, sans-serif; }

        
        
        /* Content */
        .content { margin-left: 100px; padding: 30px; }
        .card { border-radius: 10px; box-shadow: 0px 4px 8px rgba(0,0,0,0.1); }

        /* Table */
        .table thead { background: #023d7d; color: white; }
        .table tbody tr:hover { background: rgba(0, 0, 0, 0.05); }

        /* Buttons */
        .btn-primary, .btn-success { border-radius: 5px; font-size: 15px; }

        /* Modal */
        .modal-content { border-radius: 10px; }
    </style>
</head>
<body>



<!-- Main Content -->
<div class="content">
    <h2 class="mb-4"><i class="fas fa-chart-line"></i> Fees Management</h2>
    <p>Welcome, <strong><?php echo $_SESSION['role']; ?></strong> 👋</p>

    <!-- Fees Management Section -->
    <div class="card p-4">
        <h3><i class="fas fa-money-check-alt"></i> Fees Management</h3>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addFeeModal">
            ➕ Add Fee
        </button>

        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $fees_query = "SELECT fees.id, users.fullname AS student_name, fees.amount, fees.status, fees.due_date
                               FROM fees 
                               JOIN students ON fees.student_id = students.id 
                               JOIN users ON students.user_id = users.id";
                $fees_result = $conn->query($fees_query);

                while ($fee = $fees_result->fetch_assoc()):
                ?>
                <tr>
                    <td><?= htmlspecialchars($fee['student_name']); ?></td>
                    <td>LKR<?= number_format($fee['amount'], 2); ?></td>
                    <td>
                        <span class="badge <?= ($fee['status'] == 'Paid') ? 'bg-success' : 'bg-danger'; ?>">
                            <?= htmlspecialchars($fee['status']); ?>
                        </span>
                    </td>
                    <td><?= $fee['due_date'] ?: date('Y-m-d'); ?></td>
                    <td>
                        <form method="POST" action="update_fee_status.php">
                            <input type="hidden" name="fee_id" value="<?= $fee['id']; ?>">
                            <button type="submit" class="btn btn-success btn-sm">Mark as Paid</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Fee Modal -->
    <div class="modal fade" id="addFeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Fee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_fee.php" method="POST">
                    <div class="modal-body">
                        <label>Student</label>
                        <select name="student_id" class="form-control" required>
                            <?php
                            $students = $conn->query("SELECT students.id, users.fullname FROM students JOIN users ON students.user_id = users.id");
                            while ($student = $students->fetch_assoc()) {
                                echo "<option value='{$student['id']}'>" . htmlspecialchars($student['fullname']) . "</option>";
                            }
                            ?>
                        </select>

                        <label>Amount</label>
                        <input type="number" name="amount" class="form-control" required>

                        <label>Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add Fee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Graph for Paid vs Due Fees -->
    <div class="card p-4">
        <h3><i class="fas fa-chart-pie"></i> Fees Overview</h3>
        <canvas id="feesChart"></canvas>
    </div>

    <?php
    $paid_fees = $conn->query("SELECT COUNT(*) AS total FROM fees WHERE status='Paid'")->fetch_assoc()['total'];
    $due_fees = $conn->query("SELECT COUNT(*) AS total FROM fees WHERE status IN ('pending', 'overdue')")->fetch_assoc()['total'];
    ?>
</div>

<script>
var ctx = document.getElementById("feesChart").getContext("2d");
var feesChart = new Chart(ctx, {
    type: "doughnut",
    data: {
        labels: ["Paid Fees", "Due Fees"],
        datasets: [{
            data: [<?= $paid_fees; ?>, <?= $due_fees; ?>],
            backgroundColor: ["#28a745", "#dc3545"]
        }]
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
