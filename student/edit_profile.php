<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$student_id = $_SESSION['user_id'];

// Fetch student details
$query = "SELECT fullname, email, profile_pic FROM users WHERE id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);

    if (empty($fullname) || empty($email)) {
        $_SESSION['error'] = "Full name and email cannot be empty.";
        header("Location: edit_profile.php");
        exit();
    }

    // Update name and email
    $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $fullname, $email, $student_id);
    $stmt->execute();

    // Update profile picture if new image is posted
    if (!empty($_POST['cropped_image'])) {
        $image_data = $_POST['cropped_image'];
        $image_parts = explode(";base64,", $image_data);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);

        $allowed_types = ['jpeg', 'jpg', 'png'];
        if (!in_array($image_type, $allowed_types)) {
            $_SESSION['error'] = "Invalid file type! Only JPG, JPEG, and PNG allowed.";
            header("Location: edit_profile.php");
            exit();
        }

        $file_name = "profile_" . $student_id . "." . $image_type;
        $file_path = "uploads/" . $file_name;
        file_put_contents($file_path, $image_base64);

        $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param("si", $file_name, $student_id);
        $stmt->execute();
    }

    // Update password if provided
    if (!empty($_POST['new_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = "Passwords do not match!";
            header("Location: edit_profile.php");
            exit();
        }

        if (strlen($new_password) < 6) {
            $_SESSION['error'] = "Password must be at least 6 characters.";
            header("Location: edit_profile.php");
            exit();
        }

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $student_id);
        $stmt->execute();
    }

    $_SESSION['success'] = "Profile updated successfully!";
    header("Location: edit_profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <style>
        .profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }
        .preview-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        #preview {
            max-width: 100%;
            max-height: 300px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Profile</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($student['fullname']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']); ?>" required>
            </div>
            <div class="mb-3 text-center">
                <label class="form-label">Profile Picture</label><br>
                <img id="current-profile" src="uploads/<?= htmlspecialchars($student['profile_pic'] ?: 'default.png'); ?>" class="profile-pic mb-2" onerror="this.onerror=null;this.src='uploads/default.png';">
                <input type="file" id="profile-pic" class="form-control" accept="image/jpeg, image/jpg, image/png">
            </div>
            <div class="preview-container">
                <img id="preview" src="#" style="display: none;">
                <button type="button" id="crop-btn" class="btn btn-primary" style="display: none;">Crop</button>
            </div>
            <input type="hidden" name="cropped_image" id="cropped-image">

            <hr>
            <h5>Change Password (optional)</h5>
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control">
            </div>

            <button type="submit" class="btn btn-success mt-3">Update Profile</button>
        </form>

        <a href="dashboardstudent.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>

    <script>
        let cropper;
        document.getElementById("profile-pic").addEventListener("change", function(event) {
            const file = event.target.files[0];
            if (file) {
                const validTypes = ["image/jpeg", "image/jpg", "image/png"];
                if (!validTypes.includes(file.type)) {
                    alert("Invalid file type! Only JPG, JPEG, and PNG allowed.");
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById("preview");
                    preview.src = e.target.result;
                    preview.style.display = "block";
                    document.getElementById("crop-btn").style.display = "inline-block";

                    if (cropper) {
                        cropper.destroy();
                    }
                    cropper = new Cropper(preview, {
                        aspectRatio: 1,
                        viewMode: 2,
                    });
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById("crop-btn").addEventListener("click", function() {
            const canvas = cropper.getCroppedCanvas();
            document.getElementById("preview").src = canvas.toDataURL();
            document.getElementById("cropped-image").value = canvas.toDataURL();
        });
    </script>
</body>
</html>
