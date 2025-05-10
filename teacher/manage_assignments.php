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

// Fetch assignments created by this teacher
$sql = "SELECT assignments.*, courses.course_name 
        FROM assignments 
        JOIN courses ON assignments.course_id = courses.id 
        WHERE assignments.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assignments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center mb-4">📚 Manage Assignments</h2>

    <!-- Add Assignment Button -->
    <div class="d-flex justify-content-end mb-3">
        <a href="add_assignment.php" class="btn btn-primary">➕ Add New Assignment</a>
    </div>

    <!-- Assignments Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Course</th>
                    <th>Deadline</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']); ?></td>
                        <td><?= htmlspecialchars($row['description']); ?></td>
                        <td><?= htmlspecialchars($row['course_name']); ?></td>
                        <td><?= htmlspecialchars($row['deadline']); ?></td>
                        <td>
                            <a href="edit_assignment.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                            <a href="delete_assignment.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">🗑️ Delete</a>
                        </td>
                    </tr>

                    <!-- Fetch and Display Submitted Assignments for Each Assignment -->
                    <?php
                    $assignment_id = $row['id'];
                    $submission_query = "SELECT submissions.id, submissions.file_path, submissions.submission_date, 
                                                students.fullname AS student_name, submissions.grade, submissions.feedback
                                         FROM submissions 
                                         JOIN students ON submissions.student_id = students.id 
                                         WHERE submissions.assignment_id = ?";
                    $stmt_sub = $conn->prepare($submission_query);
                    $stmt_sub->bind_param("i", $assignment_id);
                    $stmt_sub->execute();
                    $submission_result = $stmt_sub->get_result();
                    
                    if ($submission_result->num_rows > 0) { ?>
                        <tr>
                            <td colspan="5">
                                <strong>Submitted Assignments:</strong>
                                <table class="table table-bordered mt-2">
                                    <thead>
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Submitted File</th>
                                            <th>Submission Date</th>
                                            <th>Grade</th>
                                            <th>Feedback</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($submission = $submission_result->fetch_assoc()) { ?>
                                            <tr>
                                                <td><?= htmlspecialchars($submission['student_name']); ?></td>
                                                <td>
                                                    <a href="<?= htmlspecialchars($submission['file_path']); ?>" download class="btn btn-info btn-sm">📥 Download</a>
                                                </td>
                                                <td><?= htmlspecialchars($submission['submission_date']); ?></td>
                                                <td><?= htmlspecialchars($submission['grade'] ?? 'Not Graded'); ?></td>
                                                <td><?= htmlspecialchars($submission['feedback'] ?? 'No Feedback'); ?></td>
                                                <td>

                                                <form action="grade_assignment.php" method="POST" onsubmit="return validateGradeForm(this);">

                                                        <input type="hidden" name="submission_id" value="<?= $submission['id']; ?>">
                                                        <input type="text" name="grade" placeholder="Grade" required class="form-control mb-2">
                                                        <textarea name="feedback" placeholder="Feedback" required class="form-control mb-2"></textarea>
                                                        <button type="submit" class="btn btn-success btn-sm">✅ Submit Grade</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function validateGradeForm(form) {
    let grade = form.grade.value.trim();
    let feedback = form.feedback.value.trim();
    let submission_id = form.submission_id.value;

    if (grade === "" || feedback === "") {
        alert("⚠️ Please enter both grade and feedback.");
        return false;
    }

    if (!submission_id) {
        alert("⚠️ Error: Missing submission ID.");
        return false;
    }

    return true;
}
</script>

</body>
</html>
