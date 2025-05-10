<?php
session_start();
include 'db_connect.php';
include 'auth.php';
checkRole('admin');

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $application_id = $_POST['application_id'];
    $new_status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $application_id);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Status updated successfully'); window.location='admin_applications.php';</script>";
    } else {
        echo "<script>alert('❌ Failed to update status');</script>";
    }
}

$applications = $conn->query("SELECT applications.*, courses.course_name 
                              FROM applications 
                              JOIN courses ON applications.course_id = courses.id 
                              ORDER BY applied_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Application Dashboard - IPHS Campus</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .Pending { background-color: #ffc107; color: black; }
        .Accepted { background-color: #28a745; color: white; }
        .Rejected { background-color: #dc3545; color: white; }
    </style>
</head>
<body class="container mt-5">
    <h2 class="text-center text-primary mb-4">📋 Application Management Dashboard</h2>

    <div class="card p-3">
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Course</th>
                    <th>Document</th>
                    <th>Status</th>
                    <th>Change Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $applications->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['fullname']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td><?= htmlspecialchars($row['phone']); ?></td>
                        <td><?= htmlspecialchars($row['course_name']); ?></td>
                        <td>
                            <a href="uploads/<?= $row['document']; ?>" target="_blank" class="btn btn-sm btn-secondary">📎 View</a>
                        </td>
                        <td>
                            <span class="status-badge <?= $row['status']; ?>">
                                <?= $row['status']; ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="application_id" value="<?= $row['id']; ?>">
                                <select name="status" class="form-select form-select-sm mb-2">
                                    <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Accepted" <?= $row['status'] == 'Accepted' ? 'selected' : ''; ?>>Accepted</option>
                                    <option value="Rejected" <?= $row['status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm btn-primary">✔️ Update</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
                <?php if ($applications->num_rows == 0): ?>
                    <tr><td colspan="7">No applications found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
