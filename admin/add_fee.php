<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $amount = $_POST['amount'];
    
    // If due date is empty, set it to today's date
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : date('Y-m-d');

    // Default status is "Due"
    $status = "Due";

    $query = "INSERT INTO fees (student_id, amount, status, due_date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("idss", $student_id, $amount, $status, $due_date);

    if ($stmt->execute()) {
        echo "<script>alert('Fee added successfully!'); window.location='dashboardadmin.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
