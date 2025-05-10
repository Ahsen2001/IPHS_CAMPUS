<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST["fullname"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = $_POST["role"];
    $profile_pic = "default.png";

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['error'] = "Email already exists!";
        header("Location: register.php");
        exit();
    }
    $check->close();

    // Insert into users table
    $query = $conn->prepare("INSERT INTO users (fullname, email, password, role, profile_pic) VALUES (?, ?, ?, ?, ?)");
    $query->bind_param("sssss", $fullname, $email, $password, $role, $profile_pic);
    
    if ($query->execute()) {
        $user_id = $query->insert_id;
        $query->close();

        // Insert into respective role table
        if ($role == "student") {
            $class = $_POST["class"];
            $stmt = $conn->prepare("INSERT INTO students (user_id, fullname, class) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $fullname, $class);
            $stmt->execute();
            $stmt->close();

        } elseif ($role == "teacher") {
            $teacher_id = "TCH" . str_pad($user_id, 3, "0", STR_PAD_LEFT);
            $stmt = $conn->prepare("INSERT INTO teachers (user_id, name, email, teacher_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $fullname, $email, $teacher_id);
            $stmt->execute();
            $stmt->close();
        }

        $_SESSION['success'] = "Registration successful!";
        header("Location: login.php");
        exit();

    } else {
        $_SESSION['error'] = "Something went wrong during registration.";
        header("Location: register.php");
        exit();
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - IPHS Campus</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to right, #007bff, #6610f2);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .form-control, .btn {
            border-radius: 10px;
        }
        .hidden {
            display: none;
        }
        #profilePreview {
            max-width: 120px;
            max-height: 120px;
            margin-top: 10px;
            border-radius: 10px;
            display: none;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card p-4 bg-light">
        <h2 class="text-center text-primary">Create Your Account</h2>
        <form method="POST" action="register.php" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="fullname" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Upload Profile Picture</label>
                <input type="file" name="profile_pic" class="form-control" accept="image/*" onchange="previewImage(event)">
                <img id="profilePreview" alt="Profile Preview">
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" id="roleSelect" class="form-control" required>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div id="student_fields" class="hidden">
                <h5 class="text-primary">Student Details</h5>

                <label>Class</label>
                <input type="text" name="class" class="form-control mb-2" required>

                <label>Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control mb-2">

                <label>Gender</label>
                <select name="gender" class="form-control mb-2">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>

                <label>Guardian Name</label>
                <input type="text" name="guardian_name" class="form-control mb-2">

                <label>Guardian Contact</label>
                <input type="text" name="guardian_contact" class="form-control mb-2">
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
    </div>
</div>

<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = () => {
        const preview = document.getElementById('profilePreview');
        preview.src = reader.result;
        preview.style.display = "block";
    };
    reader.readAsDataURL(event.target.files[0]);
}

document.addEventListener("DOMContentLoaded", function() {
    const roleSelect = document.getElementById("roleSelect");
    const studentFields = document.getElementById("student_fields");

    function toggleFields() {
        studentFields.style.display = roleSelect.value === "student" ? "block" : "none";
    }

    toggleFields();
    roleSelect.addEventListener("change", toggleFields);
});
</script>

</body>
</html>
