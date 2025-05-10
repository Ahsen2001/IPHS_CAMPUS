<?php
include 'db_connect.php';

// Fetch available courses for selection
$course_query = "SELECT * FROM courses";
$course_result = $conn->query($course_query);

session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $course_id = $_POST['course_id'];

    // Handle File Upload
    $document = "default.pdf"; 
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $upload_dir = "uploads/";
        $file_name = basename($_FILES['document']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = uniqid("DOC_", true) . "." . $file_ext;
        $target_file = $upload_dir . $new_file_name;

        $allowed_types = ["pdf", "jpg", "jpeg", "png"];
        if (in_array($file_ext, $allowed_types) && move_uploaded_file($_FILES['document']['tmp_name'], $target_file)) {
            $document = $new_file_name;
        }
    }

    // Insert into applications table
    $query = "INSERT INTO applications (fullname, email, phone, course_id, document) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssis", $fullname, $email, $phone, $course_id, $document);

    if ($stmt->execute()) {
        echo "<script>alert('Application Submitted Successfully!'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Error submitting application: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Application - IPHS Campus</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        /* General Styles */
        body {
            background: linear-gradient(to right, #007bff, #6610f2);
            font-family: Arial, sans-serif;
            color: white;
            padding-top: 50px;
        }

        .container {
            max-width: 800px;
            background: white;
            color: black;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-control, .btn {
            border-radius: 10px;
        }

        .form-label {
            font-weight: bold;
            color: #023d7d;
        }

        .submit-btn {
            width: 100%;
            font-size: 1.1rem;
            font-weight: bold;
            padding: 12px;
            transition: 0.3s;
        }

        .submit-btn:hover {
            background: #6610f2;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center text-primary">📋 Online Application Form</h2>
    <p class="text-center">Fill in your details to apply for a course at IPHS Campus.</p>

    <form action="process_application.php" method="POST" enctype="multipart/form-data">
        
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="fullname" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Phone Number</label>
            <input type="tel" name="phone" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Select Course</label>
            <select name="course_id" class="form-control" required>
                <option value="">-- Choose a Course --</option>
                <?php while ($course = $course_result->fetch_assoc()): ?>
                    <option value="<?= $course['id']; ?>"><?= htmlspecialchars($course['course_name']); ?> (<?= htmlspecialchars($course['course_level']); ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Upload CV / ID (PDF, JPG, PNG)</label>
            <input type="file" name="document" class="form-control" accept=".pdf, .jpg, .jpeg, .png" required>
        </div>

        <button type="submit" class="btn btn-primary submit-btn">Submit Application</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
