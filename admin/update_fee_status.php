<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['fee_id'])) {
    $fee_id = $_POST['fee_id'];

    $stmt = $conn->prepare("UPDATE fees SET status = 'Paid' WHERE id = ?");
    $stmt->bind_param("i", $fee_id);

    if ($stmt->execute()) {
        echo "<script>alert('Fee marked as Paid!'); window.location='dashboardadmin.php';</script>";
    } else {
        echo "<script>alert('Error updating fee!'); window.location='dashboardadmin.php';</script>";
    }
}
?>
