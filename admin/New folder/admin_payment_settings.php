<?php
session_start();
include 'db_connect.php';
include 'auth.php';
checkRole('admin'); // Ensure only admins can access

// ✅ Fetch Existing Payment Gateways
$query = "SELECT * FROM payment_config";
$result = $conn->query($query);

// ✅ Handle Form Submission for Updating Payment Configurations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gateway_id = $_POST['gateway_id'];
    $merchant_id = $_POST['merchant_id'];
    $secret_key = $_POST['secret_key'];
    $status = $_POST['status'];

    $update_query = "UPDATE payment_config SET merchant_id = ?, secret_key = ?, status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssi", $merchant_id, $secret_key, $status, $gateway_id);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Payment gateway settings updated successfully!'); window.location='admin_payment_settings.php';</script>";
    } else {
        echo "<script>alert('⚠️ Error updating payment settings!');</script>";
    }
}

// ✅ Handle New Payment Gateway Addition
if (isset($_POST['add_gateway'])) {
    $gateway_name = $_POST['gateway_name'];
    $merchant_id = $_POST['merchant_id'];
    $secret_key = $_POST['secret_key'];
    $status = $_POST['status'];

    $insert_query = "INSERT INTO payment_config (gateway_name, merchant_id, secret_key, status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssss", $gateway_name, $merchant_id, $secret_key, $status);

    if ($stmt->execute()) {
        echo "<script>alert('✅ New Payment Gateway Added Successfully!'); window.location='admin_payment_settings.php';</script>";
    } else {
        echo "<script>alert('⚠️ Error adding new payment gateway!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Payment Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center text-primary">💳 Manage Payment Gateways</h2>

        <!-- ✅ Payment Gateway Table -->
        <div class="card p-4 mb-4">
            <h4>📋 Existing Payment Gateways</h4>
            <table class="table table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Gateway</th>
                        <th>Merchant ID</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <form method="POST">
                                <td><?= htmlspecialchars($row['gateway_name']); ?></td>
                                <td><input type="text" name="merchant_id" value="<?= htmlspecialchars($row['merchant_id']); ?>" class="form-control"></td>
                                <td>
                                    <select name="status" class="form-select">
                                        <option value="enabled" <?= $row['status'] === 'enabled' ? 'selected' : ''; ?>>Enabled</option>
                                        <option value="disabled" <?= $row['status'] === 'disabled' ? 'selected' : ''; ?>>Disabled</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="gateway_id" value="<?= $row['id']; ?>">
                                    <button type="submit" class="btn btn-success btn-sm">✅ Save</button>
                                </td>
                            </form>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- ✅ Add New Payment Gateway -->
        <div class="card p-4">
            <h4>➕ Add New Payment Gateway</h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Gateway Name</label>
                    <input type="text" name="gateway_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Merchant ID</label>
                    <input type="text" name="merchant_id" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Secret Key</label>
                    <input type="text" name="secret_key" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="enabled">Enabled</option>
                        <option value="disabled">Disabled</option>
                    </select>
                </div>
                <button type="submit" name="add_gateway" class="btn btn-primary w-100">➕ Add Gateway</button>
            </form>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
