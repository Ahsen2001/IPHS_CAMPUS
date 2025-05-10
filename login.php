<?php
session_start();
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = trim($_POST['username_email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    $query = $conn->prepare("SELECT * FROM users WHERE fullname = ? OR email = ?");
    $query->bind_param("ss", $input, $input);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8');

        if ($remember) {
            setcookie("remember_me", $user['id'], time() + (86400 * 30), "/"); // 30 days
        }

        // Redirect based on role
        $dashboard = [
            "admin" => "admin/dashboardadmin.php",
            "teacher" => "teacher/dashboardteacher.php",
            "student" => "student/dashboardstudent.php"
        ];
        header("Location: " . ($dashboard[$user['role']] ?? 'dashboard.php'));
        exit();
    } else {
        $error = "Invalid login credentials.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - IPHS Campus</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: url('images/iphs1.jpg') no-repeat center center/cover;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }
        .login-container {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 40px 30px;
            width: 100%;
            max-width: 380px;
            text-align: center;
            color: #fff;
        }
        .inputbox {
            position: relative;
            margin-bottom: 20px;
        }
        .inputbox input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border-radius: 25px;
            border: none;
            font-size: 15px;
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #333;
        }
        .btn-login {
            background: #2a5298;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 25px;
            width: 100%;
            font-weight: bold;
        }
        .btn-login:hover {
            background: #1e3c72;
        }
        .form-check {
            text-align: left;
        }
        @media (max-width: 500px) {
            .login-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2><i class="fa-solid fa-user-lock"></i> Login</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="inputbox">
                <input type="text" name="username_email" placeholder="Username or Email" required>
            </div>

            <div class="inputbox">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <span toggle="#password" class="fas fa-eye toggle-password"></span>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">Remember Me</label>
            </div>

            <button type="submit" class="btn btn-login">Login</button>

            <div class="mt-3">
               
                <a href="register.php" class="text-light">Register</a>
            </div>
        </form>
    </div>

    <script>
        document.querySelector('.toggle-password').addEventListener('click', function () {
            const password = document.getElementById('password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>




