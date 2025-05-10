<?php
session_start();
include("db_connect.php");
include("auth.php");
checkRole("admin"); // Ensure only admin can access

// ✅ Handle Fee Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_fee"])) {
    $fee_id = intval($_POST["fee_id"]);
    $status = $_POST["status"];

    // Validate status
    $valid_statuses = ["Paid", "Pending", "Overdue"];
    if (!in_array($status, $valid_statuses)) {
        echo "<script>alert('❌ Invalid status selected!');</script>";
    } else {
        // Update fee status
        $query = "UPDATE fees SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $fee_id);

        if ($stmt->execute()) {
            echo "<script>alert('✅ Fee status updated successfully!'); window.location='admin_update_fee.php';</script>";
        } else {
            echo "<script>alert('❌ Error updating fee status.');</script>";
        }
    }
}

// ✅ Fetch all fee records
$fees_query = "SELECT fees.id, fees.amount, fees.status, fees.due_date, students.fullname AS student_name 
               FROM fees 
               JOIN students ON fees.student_id = students.id 
               ORDER BY fees.due_date ASC";
$fees_result = $conn->query($fees_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Update Fees</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center text-primary">💰 Manage Student Fees</h2>

    <!-- Fee Update Table -->
    <div class="card p-4">
        <h3>📋 Student Fee Records</h3>
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>Student</th>
                    <th>Amount ($)</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fee = $fees_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($fee['student_name']); ?></td>
                        <td>$<?= number_format($fee['amount'], 2); ?></td>
                        <td>
                            <span class="badge bg-<?= $fee['status'] == 'Paid' ? 'success' : ($fee['status'] == 'Pending' ? 'warning' : 'danger'); ?>">
                                <?= htmlspecialchars($fee['status']); ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($fee['due_date']); ?></td>
                        <td>
                            <form method="POST" class="d-flex">
                                <input type="hidden" name="fee_id" value="<?= $fee['id']; ?>">
                                <select name="status" class="form-select form-select-sm me-2">
                                    <option value="Paid" <?= $fee['status'] == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                                    <option value="Pending" <?= $fee['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Overdue" <?= $fee['status'] == 'Overdue' ? 'selected' : ''; ?>>Overdue</option>
                                </select>
                                <button type="submit" name="update_fee" class="btn btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
