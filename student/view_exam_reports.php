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

// Fetch exams created by the teacher
$exams_query = "SELECT exams.id, exams.title, courses.course_name 
                FROM exams 
                JOIN courses ON exams.course_id = courses.id 
                WHERE exams.teacher_id = ?";
$stmt = mysqli_prepare($conn, $exams_query);
mysqli_stmt_bind_param($stmt, "i", $teacher_id);
mysqli_stmt_execute($stmt);
$exams_result = mysqli_stmt_get_result($stmt);

$student_names = [];
$marks_obtained = [];
$percentile_ranks = [];
$class_average = 0;

if (isset($_POST['exam_id'])) {
    $exam_id = $_POST['exam_id'];

    // Fetch student results
    $results_query = "SELECT students.name, exam_results.marks_obtained 
                      FROM exam_results 
                      JOIN students ON exam_results.student_id = students.id 
                      WHERE exam_results.exam_id = ?";
    $stmt = mysqli_prepare($conn, $results_query);
    mysqli_stmt_bind_param($stmt, "i", $exam_id);
    mysqli_stmt_execute($stmt);
    $results_result = mysqli_stmt_get_result($stmt);

    $total_marks = 0;
    $student_count = 0;
    $marks_list = [];

    while ($row = mysqli_fetch_assoc($results_result)) {
        $student_names[] = $row['name'];
        $marks_obtained[] = $row['marks_obtained'];
        $marks_list[] = $row['marks_obtained'];
        $total_marks += $row['marks_obtained'];
        $student_count++;
    }

    // Calculate class average
    if ($student_count > 0) {
        $class_average = round($total_marks / $student_count, 2);
    }

    // Calculate percentile rank
    sort($marks_list);
    foreach ($marks_obtained as $mark) {
        $percentile = round((array_search($mark, $marks_list) / ($student_count - 1)) * 100, 1);
        $percentile_ranks[] = $percentile;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Exam Performance Reports</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="container mt-5">
    <h2 class="text-primary">📊 Exam Performance Reports</h2>

    <form method="POST" action="" class="card p-3 shadow-lg">
        <label class="form-label">📚 Select Exam:</label>
        <select name="exam_id" class="form-select mb-3" required onchange="this.form.submit()">
            <option value="">-- Select Exam --</option>
            <?php while ($exam = mysqli_fetch_assoc($exams_result)) { ?>
                <option value="<?php echo $exam['id']; ?>" 
                <?php echo (isset($_POST['exam_id']) && $_POST['exam_id'] == $exam['id']) ? 'selected' : ''; ?>>
                    <?php echo $exam['course_name'] . " - " . $exam['title']; ?>
                </option>
            <?php } ?>
        </select>
    </form>

    <?php if (isset($_POST['exam_id'])) { ?>
        <h3>📊 Exam Performance Chart</h3>
        <canvas id="examChart"></canvas>

        <h3 class="mt-4">📋 Exam Results Table</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>👩‍🎓 Student</th>
                    <th>📊 Marks Obtained</th>
                    <th>📈 Percentile Rank</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < count($student_names); $i++) { 
                    $color = "black";
                    if ($percentile_ranks[$i] >= 90) $color = "green"; // Top 10%
                    elseif ($percentile_ranks[$i] <= 10) $color = "red"; // Bottom 10%
                ?>
                    <tr style="color: <?php echo $color; ?>">
                        <td><?php echo $student_names[$i]; ?></td>
                        <td><?php echo $marks_obtained[$i]; ?></td>
                        <td><?php echo $percentile_ranks[$i]; ?>%</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <h4 class="text-success">📈 Class Average: <?php echo $class_average; ?>%</h4>

        <script>
            var studentNames = <?php echo json_encode($student_names); ?>;
            var marksObtained = <?php echo json_encode($marks_obtained); ?>;
            var percentileRanks = <?php echo json_encode($percentile_ranks); ?>;
            var classAverage = <?php echo $class_average; ?>;

            // Set colors based on percentile ranks
            var barColors = percentileRanks.map(percentile => 
                percentile >= 90 ? 'rgba(0, 255, 0, 0.8)' : // Green for Top 10%
                percentile <= 10 ? 'rgba(255, 0, 0, 0.8)' : // Red for Bottom 10%
                'rgba(54, 162, 235, 0.8)' // Blue for others
            );

            var ctx = document.getElementById('examChart').getContext('2d');
            var examChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: studentNames,
                    datasets: [{
                        label: 'Marks Obtained',
                        data: marksObtained,
                        backgroundColor: barColors,
                        borderColor: 'rgba(0, 0, 0, 0.5)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    },
                    plugins: {
                        annotation: {
                            annotations: [{
                                type: 'line',
                                mode: 'horizontal',
                                scaleID: 'y',
                                value: classAverage,
                                borderColor: 'rgba(255, 165, 0, 1)', // Orange for average line
                                borderWidth: 2,
                                label: {
                                    content: 'Class Average',
                                    enabled: true,
                                    position: 'right'
                                }
                            }]
                        }
                    }
                }
            });
        </script>
    <?php } ?>

</body>
</html>
