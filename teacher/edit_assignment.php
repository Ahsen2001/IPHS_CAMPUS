<?php
session_start();
include 'db_connect.php';
include 'auth.php';

// Ensure only teachers can access
if ($_SESSION['role'] !== 'teacher') {
    header("Location: index.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$assignment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch assignment details
$sql = "SELECT * FROM assignments WHERE id = '$assignment_id' AND user_id = '$teacher_id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Unauthorized access or assignment not found.";
    header("Location: manage_assignments.php");
    exit();
}

$assignment = mysqli_fetch_assoc($result);

// Update assignment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $deadline = $_POST['deadline'];

    $update_sql = "UPDATE assignments SET title = '$title', description = '$description', deadline = '$deadline' WHERE id = '$assignment_id' AND user_id = '$teacher_id'";
    
    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['success'] = "Assignment updated successfully.";
    } else {
        $_SESSION['error'] = "Error updating assignment.";
    }
    header("Location: manage_assignments.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Assignment</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <h2 class="text-center text-primary">✏️ Edit Assignment</h2>

        <!-- Display messages -->
        <?php if (isset($_SESSION['error'])) { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>

        <?php if (isset($_SESSION['success'])) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Title:</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($assignment['title']); ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description:</label>
                <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($assignment['description']); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Deadline:</label>
                <input type="date" name="deadline" value="<?php echo $assignment['deadline']; ?>" class="form-control" required>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-success">✅ Update Assignment</button>
                <a href="manage_assignments.php" class="btn btn-secondary">🔙 Back</a>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
