<?php
session_start();
include 'db_connect.php';
include 'auth.php';
checkRole('student');

$user_id = $_SESSION['user_id'];

// ✅ Fetch Student ID
$student_query = "SELECT id FROM students WHERE user_id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("<script>alert('⚠️ Error: Student record not found! Please contact admin.'); window.location='dashboard_student.php';</script>");
}

$student_id = $student['id']; 

// ✅ Fetch Assignments for Enrolled Courses
$assignments_query = "SELECT a.id, a.title, a.description, a.deadline, 
                             c.course_name, a.file_path 
                      FROM assignments a
                      JOIN courses c ON a.course_id = c.id 
                      JOIN enrollments e ON e.course_id = c.id
                      WHERE e.student_id = ?
                      ORDER BY a.deadline ASC";
$stmt = $conn->prepare($assignments_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$assignments_result = $stmt->get_result();

// ✅ Fetch Submitted Assignments (WITH FILES, Grades & Feedback)
$submitted_query = "SELECT s.assignment_id, s.file_path AS submitted_file, s.grade, s.feedback 
                    FROM submissions s
                    WHERE s.student_id = ?";
$stmt = $conn->prepare($submitted_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$submitted_result = $stmt->get_result();

// Store submitted assignments in an array
$submitted_assignments = [];
while ($row = $submitted_result->fetch_assoc()) {
    $submitted_assignments[$row['assignment_id']] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Assignments</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fd; }
        .container { max-width: 900px; margin-top: 50px; }
        .card { border-radius: 12px; box-shadow: 0px 6px 12px rgba(0,0,0,0.1); }
        .btn:hover { transform: translateY(-2px); }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-primary text-center">📝 My Assignments</h2>

    <div class="card p-4">
        <h3>📚 Available Assignments</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>📚 Course</th>
                    <th>📝 Title</th>
                    <th>📅 Deadline</th>
                    <th>📥 Download</th>
                    <th>📤 Submit</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $assignments_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['course_name']); ?></td>
                        <td><?= htmlspecialchars($row['title']); ?></td>
                        <td><?= htmlspecialchars($row['deadline']); ?></td>
                        <td>
                            <?php if (!empty($row['file_path']) && file_exists($row['file_path'])) { ?>
                                <a href="<?= htmlspecialchars($row['file_path']); ?>" download class="btn btn-success btn-sm">📥 Download</a>
                            <?php } else { ?>
                                <span class="text-danger">File Not Found</span>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if (isset($submitted_assignments[$row['id']])) { ?>
                                ✅ Submitted
                            <?php } else { ?>
                                <form action="submit_assignment.php" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="assignment_id" value="<?= $row['id']; ?>">
                                    <input type="file" name="submission_file" accept=".pdf,.doc,.docx" required>
                                    <button type="submit" class="btn btn-primary btn-sm">📤 Submit</button>
                                </form>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- ✅ Submitted Assignments, Download, Grades & Feedback -->
    <div class="card p-4 mt-4">
        <h3>📊 Submitted Assignments & Feedback</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>📚 Assignment</th>
                    <th>📄 Submitted File</th>
                    <th>🎓 Grade</th>
                    <th>💬 Feedback</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submitted_assignments as $assignment_id => $submission) { ?>
                    <tr>
                        <td><?= htmlspecialchars($assignment_id); ?></td>
                        <td>
                            <?php if (!empty($submission['submitted_file']) && file_exists($submission['submitted_file'])) { ?>
                                <a href="<?= htmlspecialchars($submission['submitted_file']); ?>" download class="btn btn-info btn-sm">📥 View</a>
                            <?php } else { ?>
                                <span class="text-danger">File Not Found</span>
                            <?php } ?>
                        </td>
                        <td><?= $submission['grade'] ? htmlspecialchars($submission['grade']) : 'Pending'; ?></td>
                        <td><?= $submission['feedback'] ? htmlspecialchars($submission['feedback']) : 'Awaiting Review'; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>