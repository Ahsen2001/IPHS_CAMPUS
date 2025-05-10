<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location:login.php");
    exit;
}

// Role-based access restriction
if (!function_exists('checkRole')) {
    function checkRole($requiredRole) {
        if ($_SESSION['role'] !== $requiredRole) {
            echo "Access Denied!";
            exit;
        }
    }
}

// Auto-logout users after 10 minutes of inactivity
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 600)) { // 600 seconds = 10 minutes
    session_unset();
    session_destroy();
    header("Location:login.php?message=Session expired");
    exit;
}
$_SESSION['last_activity'] = time(); // Update last activity time


function checkPermission($permission) {
    include 'db_connect.php';
    $role = $_SESSION['role'];

    $query = "SELECT $permission FROM roles_permissions WHERE role = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row || !$row[$permission]) {
        die("<script>alert('🚫 Access Denied! You do not have permission to view this page.'); window.location='dashboard.php';</script>");
    }
}

?>



