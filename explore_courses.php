<?php
include 'db_connect.php';

// Fetch courses from the database
$course_query = "SELECT * FROM courses";
$course_result = $conn->query($course_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Courses - IPHS Campus</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        /* General Styling */
        body {
            background-color: #f4f7fc;
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 50px;
        }

        /* Course Cards */
        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .course-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
            overflow: hidden;
            text-align: center;
            padding: 20px;
        }

        .course-card:hover {
            transform: scale(1.05);
        }

        .course-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }

        .course-title {
            font-size: 1.5rem;
            margin: 15px 0;
            color: #023d7d;
            font-weight: bold;
        }

        .course-info {
            font-size: 1rem;
            color: #555;
        }

        .enroll-btn {
            margin-top: 10px;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
            font-weight: bold;
            background: #007bff;
            color: white;
            transition: 0.3s;
        }

        .enroll-btn:hover {
            background: #0056b3;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .course-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center text-primary">📚 Explore Our Courses</h2>
    <p class="text-center">Find the perfect course for your career goals.</p>

    <div class="course-grid">
        <?php while ($course = $course_result->fetch_assoc()): ?>
            <div class="course-card">
                <img src="images/courses.png" alt="Course Image">
                <h3 class="course-title"><?= htmlspecialchars($course['course_name']); ?></h3>
                <p class="course-info">Level: <?= htmlspecialchars($course['course_level']); ?></p>
                <p class="course-info">Fee: LKR <?= number_format($course['course_fee']); ?></p>
                <a href="enroll_course.php?course_id=<?= $course['id']; ?>" class="enroll-btn">Enroll Now</a>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
