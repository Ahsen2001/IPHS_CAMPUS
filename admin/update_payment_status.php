<?php
session_start();
include 'db_connect.php';

// ✅ Ensure Payment ID is provided
if (!isset($_GET['payment_id']) || !isset($_GET['status'])) {
    die("<script>alert('❌ Invalid request.'); window.location='fees_overview_student.php';</script>");
}

$payment_id = intval($_GET['payment_id']);
$status = $_GET['status']; // Expected values: 'success' or 'failed'

// ✅ Fetch Payment Details
$payment_query = "SELECT * FROM payments WHERE id = ?";
$stmt = $conn->prepare($payment_query);
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$payment_result = $stmt->get_result();
$payment = $payment_result->fetch_assoc();

if (!$payment) {
    die("<script>alert('❌ Payment record not found.'); window.location='fees_overview_student.php';</script>");
}

$student_id = $payment['student_id'];
$amount = $payment['amount'];
$gateway_id = $payment['gateway_id'];

// ✅ Process Payment Status
if ($status === "success") {
    // ✅ Update Payment Record as Completed
    $update_payment = "UPDATE payments SET status = 'Completed', updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($update_payment);
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();

    // ✅ Mark Fees as Paid (Update Fees Table)
    $update_fees = "UPDATE fees SET status = 'Paid' WHERE student_id = ? AND amount = ?";
    $stmt = $conn->prepare($update_fees);
    $stmt->bind_param("id", $student_id, $amount);
    $stmt->execute();

    echo "<script>alert('✅ Payment Successful!'); window.location='fees_overview_student.php';</script>";
} else {
    // ✅ Update Payment Record as Failed
    $update_payment = "UPDATE payments SET status = 'Failed', updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($update_payment);
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();

    echo "<script>alert('⚠️ Payment Failed. Please try again.'); window.location='fees_overview_student.php';</script>";
}

$stmt->close();
$conn->close();
?>
